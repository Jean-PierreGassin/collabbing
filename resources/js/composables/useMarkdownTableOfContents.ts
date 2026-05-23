import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import type { MarkdownHeading } from '@/lib/markdown';

export interface MarkdownTableOfContentsOptions {
  contentId: string;
  enabled?: boolean;
  headings: MarkdownHeading[];
}

export function useMarkdownTableOfContents(options: MarkdownTableOfContentsOptions) {
  const isMobileOpen = ref(false);
  const isContentInView = ref(false);
  const activeAnchor = ref('');

  let headingObserver: IntersectionObserver | null = null;
  let contentObserver: IntersectionObserver | null = null;
  let observeTimer: number | null = null;
  let navigationTimer: number | null = null;
  let isNavigationLocked = false;

  const mobileContentsId = computed(() => `mobile-${options.contentId}-contents`);
  const shouldShowMobileToc = computed(() => Boolean(options.enabled) && options.headings.length > 0 && isContentInView.value);

  const minimumHeadingLevel = computed(() => {
    if (options.headings.length === 0) {
      return 1;
    }

    return Math.min(...options.headings.map((heading) => heading.level));
  });

  const activeHeadingIndex = computed(() => options.headings.findIndex((heading) => heading.anchor === activeAnchor.value));

  const activePathAnchors = computed(() => {
    const path = new Set<string>();
    const index = activeHeadingIndex.value;

    if (index === -1) {
      if (options.headings[0]) {
        path.add(options.headings[0].anchor);
      }

      return path;
    }

    const activeLevel = options.headings[index].level;
    let nextAncestorLevel = activeLevel;

    path.add(options.headings[index].anchor);

    for (let headingIndex = index - 1; headingIndex >= 0; headingIndex -= 1) {
      const heading = options.headings[headingIndex];

      if (heading.level < nextAncestorLevel) {
        path.add(heading.anchor);
        nextAncestorLevel = heading.level;
      }
    }

    return path;
  });

  function parentHeadingAnchor(index: number): string | null {
    const heading = options.headings[index];

    for (let headingIndex = index - 1; headingIndex >= 0; headingIndex -= 1) {
      if (options.headings[headingIndex].level < heading.level) {
        return options.headings[headingIndex].anchor;
      }
    }

    return null;
  }

  function headingDepth(heading: MarkdownHeading): number {
    return Math.max(0, heading.level - minimumHeadingLevel.value);
  }

  function isActiveHeading(heading: MarkdownHeading): boolean {
    return activeAnchor.value === heading.anchor;
  }

  function isHeadingVisible(index: number): boolean {
    const heading = options.headings[index];

    if (heading.level === minimumHeadingLevel.value) {
      return true;
    }

    const parentAnchor = parentHeadingAnchor(index);

    return parentAnchor !== null && activePathAnchors.value.has(parentAnchor);
  }

  function mobileChevronClass(): string | undefined {
    if (isMobileOpen.value) {
      return 'rotate-180';
    }

    return undefined;
  }

  function headingLinkClass(heading: MarkdownHeading): string {
    if (isActiveHeading(heading)) {
      return 'border-primary text-primary';
    }

    return 'border-transparent text-muted-foreground hover:border-primary/50 hover:text-white';
  }

  function headingTextClass(heading: MarkdownHeading): string | undefined {
    if (headingDepth(heading) > 0) {
      return 'text-[0.8125rem]';
    }

    return undefined;
  }

  function resetHeadingObserver(): void {
    if (observeTimer !== null) {
      window.clearTimeout(observeTimer);
      observeTimer = null;
    }

    headingObserver?.disconnect();
    headingObserver = null;
  }

  function resetContentObserver(): void {
    contentObserver?.disconnect();
    contentObserver = null;
    window.removeEventListener('scroll', updateContentVisibility);
    window.removeEventListener('resize', updateContentVisibility);
    isContentInView.value = false;
  }

  function resetNavigationLock(): void {
    if (navigationTimer !== null) {
      window.clearTimeout(navigationTimer);
      navigationTimer = null;
    }

    isNavigationLocked = false;
  }

  function lockNavigation(anchor: string): void {
    if (navigationTimer !== null) {
      window.clearTimeout(navigationTimer);
    }

    isNavigationLocked = true;
    activeAnchor.value = anchor;
    navigationTimer = window.setTimeout(() => {
      activeAnchor.value = anchor;
      isNavigationLocked = false;
      navigationTimer = null;
    }, 900);
  }

  function observeHeadings(): void {
    resetHeadingObserver();

    if (!options.enabled || options.headings.length === 0) {
      return;
    }

    const headingElements = options.headings
      .map((heading) => document.getElementById(heading.anchor))
      .filter((element): element is HTMLElement => element !== null);

    if (headingElements.length === 0) {
      return;
    }

    activeAnchor.value ||= headingElements[0].id;
    headingObserver = new IntersectionObserver((entries) => {
      if (isNavigationLocked) {
        return;
      }

      const visibleEntry = entries
        .filter((entry) => entry.isIntersecting)
        .sort((left, right) => left.boundingClientRect.top - right.boundingClientRect.top)[0];

      if (visibleEntry?.target.id) {
        activeAnchor.value = visibleEntry.target.id;
      }
    }, {
      rootMargin: '-18% 0px -65% 0px',
      threshold: [
        0,
        1,
      ],
    });

    headingElements.forEach((element) => headingObserver?.observe(element));
  }

  function updateContentVisibility(): void {
    if (!options.enabled || options.headings.length === 0) {
      isContentInView.value = false;

      return;
    }

    const contentElement = document.getElementById(options.contentId);

    if (!contentElement) {
      isContentInView.value = false;

      return;
    }

    const contentBounds = contentElement.getBoundingClientRect();
    const readingOffset = 96;

    isContentInView.value = contentBounds.top <= readingOffset && contentBounds.bottom > readingOffset;
  }

  function observeContent(): void {
    resetContentObserver();

    if (!options.enabled || options.headings.length === 0) {
      return;
    }

    const contentElement = document.getElementById(options.contentId);

    if (!contentElement) {
      return;
    }

    contentObserver = new IntersectionObserver((entries) => {
      if (!entries.some((entry) => entry.isIntersecting)) {
        isContentInView.value = false;

        return;
      }

      updateContentVisibility();
    }, {
      rootMargin: '-64px 0px -22% 0px',
      threshold: [
        0,
        0.01,
      ],
    });

    contentObserver.observe(contentElement);
    window.addEventListener('scroll', updateContentVisibility, { passive: true });
    window.addEventListener('resize', updateContentVisibility);
    updateContentVisibility();
  }

  function isMobileViewport(): boolean {
    return window.matchMedia('(max-width: 1699px)').matches;
  }

  function closeMobileAfterScroll(): void {
    if (!isMobileViewport()) {
      return;
    }

    let fallbackTimer: number | null = null;

    const close = (): void => {
      if (fallbackTimer !== null) {
        window.clearTimeout(fallbackTimer);
        fallbackTimer = null;
      }

      isMobileOpen.value = false;
    };

    window.addEventListener('scrollend', close, { once: true });
    fallbackTimer = window.setTimeout(close, 650);
  }

  function mobileCollapsedOffset(): number {
    const mobileContents = document.getElementById(mobileContentsId.value);
    const mobileTrigger = mobileContents?.querySelector('button');

    return (mobileTrigger?.getBoundingClientRect().height ?? 40) + 28;
  }

  async function openHeading(anchor: string): Promise<void> {
    isMobileOpen.value = true;
    lockNavigation(anchor);

    await nextTick();

    window.setTimeout(() => requestAnimationFrame(() => {
      const heading = document.getElementById(anchor);

      if (heading) {
        let mobileOffset = 96;

        if (isMobileViewport()) {
          mobileOffset = mobileCollapsedOffset();
        }

        window.scrollTo({
          top: heading.getBoundingClientRect().top + window.scrollY - mobileOffset,
          behavior: 'smooth',
        });

        closeMobileAfterScroll();
      }

      window.history.replaceState(null, '', `#${anchor}`);
    }), 0);
  }

  watch(() => [
    options.enabled,
    options.headings,
  ] as const, async ([
    enabled,
    headings,
  ]) => {
    if (!enabled || headings.length === 0) {
      activeAnchor.value = '';
      isMobileOpen.value = false;
      resetHeadingObserver();
      resetContentObserver();
      resetNavigationLock();

      return;
    }

    if (!headings.some((heading) => heading.anchor === activeAnchor.value)) {
      activeAnchor.value = headings[0]?.anchor ?? '';
    }

    await nextTick();
    resetHeadingObserver();
    resetContentObserver();
    observeTimer = window.setTimeout(() => {
      observeHeadings();
      observeContent();
    }, 280);
  }, { immediate: true });

  onBeforeUnmount(() => {
    resetHeadingObserver();
    resetContentObserver();
    resetNavigationLock();
  });

  return {
    headingDepth,
    headingLinkClass,
    headingTextClass,
    isHeadingVisible,
    isMobileOpen,
    mobileChevronClass,
    mobileContentsId,
    openHeading,
    shouldShowMobileToc,
  };
}
