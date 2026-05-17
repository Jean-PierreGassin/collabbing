<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import CommentList from '@/components/comments/CommentList.vue';
import IdeaCard from '@/components/ideas/IdeaCard.vue';
import IdeaSidebar from '@/components/ideas/IdeaSidebar.vue';
import { Button } from '@/components/ui/button';
import { markdownHeadings, stripGeneratedTableOfContents } from '@/lib/markdown';
import { ChevronDown, GitBranch, Pencil } from '@lucide/vue';
import type { MarkdownHeading } from '@/lib/markdown';
import type { DomainUser, Idea, IdeaApplication, IdeaComment, IdeaSupporter, Paginator } from '@/types/domain';

const props = defineProps<{
  idea: Idea;
  comments: Paginator<IdeaComment>;
  collaborator: IdeaApplication | null;
  applicant: IdeaApplication | null;
  supporter: IdeaSupporter | null;
}>();

const isPitchExpanded = ref(false);
const isMobileTocOpen = ref(false);
const isPitchContentInView = ref(false);
const activePitchAnchor = ref('');
let pitchHeadingObserver: IntersectionObserver | null = null;
let pitchContentObserver: IntersectionObserver | null = null;
let observePitchTimer: number | null = null;
let pitchNavigationTimer: number | null = null;
let isPitchNavigationLocked = false;

const mentionableUsers = computed<DomainUser[]>(() => {
  const users = [props.idea.user, ...props.idea.collaborators.map((collaborator) => collaborator.user)];
  const seen = new Set<number>();

  return users.filter((user) => {
    if (seen.has(user.id)) {
      return false;
    }

    seen.add(user.id);

    return true;
  });
});

const pitchContent = computed(() => stripGeneratedTableOfContents(props.idea.content));
const pitchHeadings = computed(() => markdownHeadings(pitchContent.value));
const pitchDescriptionId = computed(() => `idea-${props.idea.id}-description`);
const shouldShowMobileToc = computed(() => isPitchExpanded.value && pitchHeadings.value.length > 0 && isPitchContentInView.value);
const minimumPitchHeadingLevel = computed(() => {
  if (pitchHeadings.value.length === 0) {
    return 1;
  }

  return Math.min(...pitchHeadings.value.map((heading) => heading.level));
});

const activeHeadingIndex = computed(() => pitchHeadings.value.findIndex((heading) => heading.anchor === activePitchAnchor.value));
const activePathAnchors = computed(() => {
  const path = new Set<string>();
  const index = activeHeadingIndex.value;

  if (index === -1) {
    if (pitchHeadings.value[0]) {
      path.add(pitchHeadings.value[0].anchor);
    }

    return path;
  }

  const activeLevel = pitchHeadings.value[index].level;
  let nextAncestorLevel = activeLevel;

  path.add(pitchHeadings.value[index].anchor);

  for (let headingIndex = index - 1; headingIndex >= 0; headingIndex -= 1) {
    const heading = pitchHeadings.value[headingIndex];

    if (heading.level < nextAncestorLevel) {
      path.add(heading.anchor);
      nextAncestorLevel = heading.level;
    }
  }

  return path;
});

const visiblePitchHeadings = computed(() => pitchHeadings.value.filter((heading, index) => {
  if (heading.level === minimumPitchHeadingLevel.value) {
    return true;
  }

  const parentAnchor = parentHeadingAnchor(index);

  return parentAnchor !== null && activePathAnchors.value.has(parentAnchor);
}));

function parentHeadingAnchor(index: number): string | null {
  const heading = pitchHeadings.value[index];

  for (let headingIndex = index - 1; headingIndex >= 0; headingIndex -= 1) {
    if (pitchHeadings.value[headingIndex].level < heading.level) {
      return pitchHeadings.value[headingIndex].anchor;
    }
  }

  return null;
}

function headingDepth(heading: MarkdownHeading): number {
  return Math.max(0, heading.level - minimumPitchHeadingLevel.value);
}

function isActiveHeading(heading: MarkdownHeading): boolean {
  return activePitchAnchor.value === heading.anchor;
}

function resetPitchHeadingObserver(): void {
  if (observePitchTimer !== null) {
    window.clearTimeout(observePitchTimer);
    observePitchTimer = null;
  }

  pitchHeadingObserver?.disconnect();
  pitchHeadingObserver = null;
}

function resetPitchContentObserver(): void {
  pitchContentObserver?.disconnect();
  pitchContentObserver = null;
  window.removeEventListener('scroll', updatePitchContentVisibility);
  window.removeEventListener('resize', updatePitchContentVisibility);
  isPitchContentInView.value = false;
}

function resetPitchNavigationLock(): void {
  if (pitchNavigationTimer !== null) {
    window.clearTimeout(pitchNavigationTimer);
    pitchNavigationTimer = null;
  }

  isPitchNavigationLocked = false;
}

function lockPitchNavigation(anchor: string): void {
  if (pitchNavigationTimer !== null) {
    window.clearTimeout(pitchNavigationTimer);
  }

  isPitchNavigationLocked = true;
  activePitchAnchor.value = anchor;
  pitchNavigationTimer = window.setTimeout(() => {
    activePitchAnchor.value = anchor;
    isPitchNavigationLocked = false;
    pitchNavigationTimer = null;
  }, 900);
}

function observePitchHeadings(): void {
  resetPitchHeadingObserver();

  if (! isPitchExpanded.value || pitchHeadings.value.length === 0) {
    return;
  }

  const headingElements = pitchHeadings.value
    .map((heading) => document.getElementById(heading.anchor))
    .filter((element): element is HTMLElement => element !== null);

  if (headingElements.length === 0) {
    return;
  }

  activePitchAnchor.value ||= headingElements[0].id;
  pitchHeadingObserver = new IntersectionObserver((entries) => {
    if (isPitchNavigationLocked) {
      return;
    }

    const visibleEntry = entries
      .filter((entry) => entry.isIntersecting)
      .sort((left, right) => left.boundingClientRect.top - right.boundingClientRect.top)[0];

    if (visibleEntry?.target.id) {
      activePitchAnchor.value = visibleEntry.target.id;
    }
  }, {
    rootMargin: '-18% 0px -65% 0px',
    threshold: [0, 1],
  });

  headingElements.forEach((element) => pitchHeadingObserver?.observe(element));
}

function updatePitchContentVisibility(): void {
  if (! isPitchExpanded.value || pitchHeadings.value.length === 0) {
    isPitchContentInView.value = false;

    return;
  }

  const pitchElement = document.getElementById(pitchDescriptionId.value);

  if (! pitchElement) {
    isPitchContentInView.value = false;

    return;
  }

  const pitchBounds = pitchElement.getBoundingClientRect();
  const readingOffset = 96;

  isPitchContentInView.value = pitchBounds.top <= readingOffset && pitchBounds.bottom > readingOffset;
}

function observePitchContent(): void {
  resetPitchContentObserver();

  if (! isPitchExpanded.value || pitchHeadings.value.length === 0) {
    return;
  }

  const pitchElement = document.getElementById(pitchDescriptionId.value);

  if (! pitchElement) {
    return;
  }

  pitchContentObserver = new IntersectionObserver((entries) => {
    if (! entries.some((entry) => entry.isIntersecting)) {
      isPitchContentInView.value = false;

      return;
    }

    updatePitchContentVisibility();
  }, {
    rootMargin: '-64px 0px -22% 0px',
    threshold: [0, 0.01],
  });

  pitchContentObserver.observe(pitchElement);
  window.addEventListener('scroll', updatePitchContentVisibility, { passive: true });
  window.addEventListener('resize', updatePitchContentVisibility);
  updatePitchContentVisibility();
}

async function openPitchHeading(anchor: string): Promise<void> {
  const shouldWaitForExpansion = ! isPitchExpanded.value;

  isPitchExpanded.value = true;
  isMobileTocOpen.value = true;
  lockPitchNavigation(anchor);

  await nextTick();

  window.setTimeout(() => requestAnimationFrame(() => {
    const heading = document.getElementById(anchor);

    if (heading) {
      const mobileContents = document.getElementById('mobile-pitch-contents');
      const mobileOffset = window.matchMedia('(max-width: 1699px)').matches
        ? (mobileContents?.getBoundingClientRect().height ?? 0) + 16
        : 96;

      window.scrollTo({
        top: heading.getBoundingClientRect().top + window.scrollY - mobileOffset,
        behavior: 'smooth',
      });
    }

    window.history.replaceState(null, '', `#${anchor}`);
  }), shouldWaitForExpansion ? 280 : 0);
}

watch([isPitchExpanded, pitchHeadings], async () => {
  if (! isPitchExpanded.value) {
    isMobileTocOpen.value = false;
    resetPitchHeadingObserver();
    resetPitchContentObserver();
    resetPitchNavigationLock();

    return;
  }

  activePitchAnchor.value ||= pitchHeadings.value[0]?.anchor ?? '';

  await nextTick();
  resetPitchHeadingObserver();
  resetPitchContentObserver();
  observePitchTimer = window.setTimeout(() => {
    observePitchHeadings();
    observePitchContent();
  }, 280);
});

onBeforeUnmount(() => {
  resetPitchHeadingObserver();
  resetPitchContentObserver();
  resetPitchNavigationLock();
});
</script>

<template>
  <section class="flex flex-col gap-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
      <div class="flex min-w-0 flex-col gap-1">
        <h1 class="text-2xl font-semibold leading-tight text-white">Idea - {{ idea.titleDisplay }}</h1>
      </div>
      <div v-if="idea.can.update" class="flex shrink-0 flex-wrap justify-end gap-2 sm:pt-0.5">
        <Button as="a" :href="idea.routes.dashboard" size="sm">
          <GitBranch class="size-4" aria-hidden="true" />
          Manage
        </Button>
        <Button as="a" :href="idea.routes.edit" variant="outline" size="sm">
          <Pencil class="size-4" aria-hidden="true" />
          Edit
        </Button>
      </div>
    </header>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
      <div class="flex flex-col gap-4">
        <div :class="pitchHeadings.length > 0 ? 'relative flex flex-col gap-4' : undefined">
          <IdeaCard v-model:pitch-expanded="isPitchExpanded" :idea="idea" single hide-title />

          <Teleport to="body">
            <Transition name="toc-float">
              <div
                v-if="shouldShowMobileToc"
                id="mobile-pitch-contents"
                class="fixed inset-x-4 top-3 z-[70] rounded-2xl border border-border bg-background/90 px-3 py-2 shadow-lg shadow-background/35 backdrop-blur min-[1700px]:hidden"
              >
                <div class="mx-auto flex max-w-2xl flex-col">
                  <button
                    type="button"
                    class="flex w-full items-center justify-between gap-3 rounded-full border-l-2 border-primary/70 py-1.5 pl-3 pr-1 text-left text-xs font-semibold uppercase text-white transition-colors hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
                    :aria-expanded="isMobileTocOpen"
                    @click="isMobileTocOpen = !isMobileTocOpen"
                  >
                    Contents
                    <ChevronDown :class="['size-4 text-primary transition-transform duration-200', isMobileTocOpen ? 'rotate-180' : undefined]" aria-hidden="true" />
                  </button>

                  <Transition name="toc-mobile">
                    <nav v-if="isMobileTocOpen" aria-label="Pitch table of contents" class="mt-2 max-h-[min(18rem,55dvh)] overflow-y-auto overscroll-contain border-l border-border py-2 text-sm">
                      <TransitionGroup name="toc-item" tag="div" class="flex flex-col gap-1">
                        <a
                          v-for="heading in visiblePitchHeadings"
                          :key="heading.anchor"
                          :href="`#${heading.anchor}`"
                          :style="{ paddingLeft: `${0.75 + headingDepth(heading) * 0.9}rem` }"
                          :class="[
                            '-ml-px flex min-h-8 items-center border-l-2 py-1.5 pr-2 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50',
                            isActiveHeading(heading) ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:border-primary/50 hover:text-white',
                          ]"
                          @click.prevent="openPitchHeading(heading.anchor)"
                        >
                          <span class="truncate" :class="headingDepth(heading) > 0 ? 'text-[0.8125rem]' : undefined">{{ heading.title }}</span>
                        </a>
                      </TransitionGroup>
                    </nav>
                  </Transition>
                </div>
              </div>
            </Transition>
          </Teleport>

          <Transition name="toc-float">
            <aside v-if="isPitchExpanded && pitchHeadings.length > 0" class="hidden min-[1700px]:absolute min-[1700px]:inset-y-0 min-[1700px]:right-full min-[1700px]:mr-4 min-[1700px]:block min-[1700px]:w-56">
              <div class="sticky top-24 p-2">
                <h2 class="pb-2 pl-3 text-sm font-semibold text-white">Contents</h2>
                <nav aria-label="Pitch table of contents" class="flex flex-col gap-1 border-l border-border text-sm">
                  <TransitionGroup name="toc-item" tag="div" class="flex flex-col gap-1">
                    <a
                      v-for="heading in visiblePitchHeadings"
                      :key="heading.anchor"
                      :href="`#${heading.anchor}`"
                      :style="{ paddingLeft: `${0.75 + headingDepth(heading) * 0.9}rem` }"
                      :class="[
                        '-ml-px flex min-h-8 items-center border-l-2 py-1.5 pr-2 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50',
                        isActiveHeading(heading) ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:border-primary/50 hover:text-white',
                      ]"
                      @click.prevent="openPitchHeading(heading.anchor)"
                    >
                      <span class="truncate" :class="headingDepth(heading) > 0 ? 'text-[0.8125rem]' : undefined">{{ heading.title }}</span>
                    </a>
                  </TransitionGroup>
                </nav>
              </div>
            </aside>
          </Transition>
        </div>

        <template v-if="idea.can.update || collaborator">
          <CommentList :comments="comments" :comments-store="idea.routes.commentsStore" :mentionable-users="mentionableUsers" />
        </template>
      </div>

      <IdeaSidebar
        :idea="idea"
        :collaborator="collaborator"
        :applicant="applicant"
        :supporter="supporter"
      />
    </div>
  </section>
</template>
