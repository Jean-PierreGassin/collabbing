<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { ChevronDown } from '@lucide/vue';
import type { MarkdownHeading } from '@/lib/markdown';

const props = withDefaults(defineProps<{
  contentId: string;
  enabled?: boolean;
  headings: MarkdownHeading[];
  navLabel?: string;
}>(), {
  enabled: true,
  navLabel: 'Table of contents',
});

const isMobileOpen = ref(false);
const isContentInView = ref(false);
const activeAnchor = ref('');
let headingObserver: IntersectionObserver | null = null;
let contentObserver: IntersectionObserver | null = null;
let observeTimer: number | null = null;
let navigationTimer: number | null = null;
let isNavigationLocked = false;

const mobileContentsId = computed(() => `mobile-${props.contentId}-contents`);
const shouldShowMobileToc = computed(() => props.enabled && props.headings.length > 0 && isContentInView.value);
const minimumHeadingLevel = computed(() => {
  if (props.headings.length === 0) {
    return 1;
  }

  return Math.min(...props.headings.map((heading) => heading.level));
});
const activeHeadingIndex = computed(() => props.headings.findIndex((heading) => heading.anchor === activeAnchor.value));
const activePathAnchors = computed(() => {
  const path = new Set<string>();
  const index = activeHeadingIndex.value;

  if (index === -1) {
    if (props.headings[0]) {
      path.add(props.headings[0].anchor);
    }

    return path;
  }

  const activeLevel = props.headings[index].level;
  let nextAncestorLevel = activeLevel;

  path.add(props.headings[index].anchor);

  for (let headingIndex = index - 1; headingIndex >= 0; headingIndex -= 1) {
    const heading = props.headings[headingIndex];

    if (heading.level < nextAncestorLevel) {
      path.add(heading.anchor);
      nextAncestorLevel = heading.level;
    }
  }

  return path;
});
function parentHeadingAnchor(index: number): string | null {
  const heading = props.headings[index];

  for (let headingIndex = index - 1; headingIndex >= 0; headingIndex -= 1) {
    if (props.headings[headingIndex].level < heading.level) {
      return props.headings[headingIndex].anchor;
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
  const heading = props.headings[index];

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

  if (!props.enabled || props.headings.length === 0) {
    return;
  }

  const headingElements = props.headings
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
    threshold: [0, 1],
  });

  headingElements.forEach((element) => headingObserver?.observe(element));
}

function updateContentVisibility(): void {
  if (!props.enabled || props.headings.length === 0) {
    isContentInView.value = false;

    return;
  }

  const contentElement = document.getElementById(props.contentId);

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

  if (!props.enabled || props.headings.length === 0) {
    return;
  }

  const contentElement = document.getElementById(props.contentId);

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
    threshold: [0, 0.01],
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

watch(() => [props.enabled, props.headings] as const, async ([enabled, headings]) => {
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
</script>

<template>
  <Teleport to="body">
    <Transition name="toc-float">
      <div
        v-if="shouldShowMobileToc"
        :id="mobileContentsId"
        class="fixed inset-x-4 top-3 z-[70] rounded-2xl border border-border bg-background/90 px-3 py-2 shadow-lg shadow-background/35 backdrop-blur min-[1700px]:hidden"
      >
        <div class="mx-auto flex max-w-2xl flex-col">
          <button
            type="button"
            class="flex w-full items-center justify-between gap-3 rounded-full border-l-2 border-primary/70 py-1.5 pl-3 pr-1 text-left text-xs font-semibold uppercase text-white transition-colors hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
            :aria-expanded="isMobileOpen"
            @click="isMobileOpen = !isMobileOpen"
          >
            Contents
            <ChevronDown
              :class="['size-4 text-primary transition-transform duration-200', mobileChevronClass()]"
              aria-hidden="true" />
          </button>

          <Transition name="toc-mobile">
            <nav
              v-if="isMobileOpen"
              :aria-label="navLabel"
              class="scrollbar-hidden mt-2 max-h-[min(18rem,55dvh)] overflow-y-auto overscroll-contain border-l border-border py-2 text-sm">
              <div class="flex flex-col gap-1">
                <div
                  v-for="(heading, index) in headings"
                  :key="heading.anchor"
                  :class="[
                    'toc-item-shell',
                    isHeadingVisible(index) ? 'toc-item-shell-visible' : 'toc-item-shell-hidden',
                  ]"
                >
                  <a
                    :href="`#${heading.anchor}`"
                    :style="{ paddingLeft: `${0.75 + headingDepth(heading) * 0.9}rem` }"
                    :tabindex="isHeadingVisible(index) ? undefined : -1"
                    :aria-hidden="!isHeadingVisible(index)"
                    :class="[
                      '-ml-px flex min-h-8 items-center border-l-2 py-1.5 pr-2 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50',
                      headingLinkClass(heading),
                    ]"
                    @click.prevent="openHeading(heading.anchor)"
                  >
                    <span
                      class="truncate"
                      :class="headingTextClass(heading)">{{ heading.title }}</span>
                  </a>
                </div>
              </div>
            </nav>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>

  <Transition name="toc-float">
    <aside
      v-if="enabled && headings.length > 0"
      class="hidden min-[1700px]:absolute min-[1700px]:inset-y-0 min-[1700px]:right-full min-[1700px]:mr-4 min-[1700px]:block min-[1700px]:w-56"
    >
      <div class="sticky top-24 p-2">
        <h2 class="pb-2 pl-3 text-sm font-semibold text-white">Contents</h2>
        <nav
          :aria-label="navLabel"
          class="scrollbar-hidden flex max-h-[calc(100svh-10rem)] flex-col gap-1 overflow-y-auto overscroll-contain border-l border-border pb-3 text-sm">
          <div class="flex flex-col gap-1">
            <div
              v-for="(heading, index) in headings"
              :key="heading.anchor"
              :class="[
                'toc-item-shell',
                isHeadingVisible(index) ? 'toc-item-shell-visible' : 'toc-item-shell-hidden',
              ]"
            >
              <a
                :href="`#${heading.anchor}`"
                :style="{ paddingLeft: `${0.75 + headingDepth(heading) * 0.9}rem` }"
                :tabindex="isHeadingVisible(index) ? undefined : -1"
                :aria-hidden="!isHeadingVisible(index)"
                :class="[
                  '-ml-px flex min-h-8 items-center border-l-2 py-1.5 pr-2 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50',
                  headingLinkClass(heading),
                ]"
                @click.prevent="openHeading(heading.anchor)"
              >
                <span
                  class="truncate"
                  :class="headingTextClass(heading)">{{ heading.title }}</span>
              </a>
            </div>
          </div>
        </nav>
      </div>
    </aside>
  </Transition>
</template>
