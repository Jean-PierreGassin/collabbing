<script setup lang="ts">
import { ChevronDown } from '@lucide/vue';
import { useMarkdownTableOfContents } from '@/composables/useMarkdownTableOfContents';
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

const {
  headingDepth,
  headingLinkClass,
  headingTextClass,
  isHeadingVisible,
  isMobileOpen,
  mobileChevronClass,
  mobileContentsId,
  openHeading,
  shouldShowMobileToc,
} = useMarkdownTableOfContents(props);
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
              :class="[
                'size-4 text-primary transition-transform duration-200',
                mobileChevronClass(),
              ]"
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
        <h2 class="pb-2 pl-3 text-sm font-semibold text-white">
          Contents
        </h2>
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
