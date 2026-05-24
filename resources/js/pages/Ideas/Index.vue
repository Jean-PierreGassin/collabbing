<script setup lang="ts">
import { computed, ref } from 'vue';
import IdeaList from '@/components/ideas/IdeaList.vue';
import PaginationLinks from '@/components/pagination/PaginationLinks.vue';
import { Button } from '@/components/ui/button';
import { Flame, LayoutGrid, ListFilter, Rows3, SearchX, Tag, X } from '@lucide/vue';
import type { Idea, IdeaTag, Paginator } from '@/types/domain';

const props = defineProps<{
  keyword: string | null;
  selectedTag: string | null;
  popularTags: IdeaTag[];
  searchResults: Paginator<Idea> | null;
  trendingIdeas: Idea[];
  ideas: Paginator<Idea>;
}>();

type IdeaViewMode = 'detailed' | 'compact';

const ideaViewModeStorageKey = 'collabbing.ideaViewMode';
const trendingIdeaIds = computed(() => new Set(props.trendingIdeas.map((idea) => idea.id)));
const recentIdeas = computed(() => props.ideas.items.filter((idea) => !trendingIdeaIds.value.has(idea.id)));
const viewMode = ref<IdeaViewMode>(storedIdeaViewMode());
const ideaListVariant = computed(() => viewMode.value);
const hasActiveFilters = computed(() => props.keyword !== null || props.selectedTag !== null);
const resultsHeading = computed(() => {
  if (props.keyword && props.selectedTag) {
    return `Results for "${props.keyword}" tagged ${props.selectedTag}`;
  }

  if (props.keyword) {
    return `Results for "${props.keyword}"`;
  }

  return `Ideas tagged ${props.selectedTag}`;
});
const resultsDescription = computed(() => {
  if (props.keyword && props.selectedTag) {
    return 'Open ideas matching your search and selected tag, newest first.';
  }

  if (props.selectedTag) {
    return 'Open ideas using this tag, newest first.';
  }

  return 'Open ideas matching your search, newest first.';
});
const emptyResultsMessage = computed(() => {
  if (props.keyword && props.selectedTag) {
    return `No ideas matched "${props.keyword}" with ${props.selectedTag}.`;
  }

  if (props.keyword) {
    return `No ideas matched "${props.keyword}".`;
  }

  return `No ideas are tagged ${props.selectedTag} yet.`;
});
const clearTagUrl = computed(() => {
  if (! props.keyword) {
    return '/ideas';
  }

  const params = new URLSearchParams();
  params.set('search', props.keyword);

  return `/ideas?${params.toString()}`;
});

function storedIdeaViewMode(): IdeaViewMode {
  if (typeof window === 'undefined') {
    return 'compact';
  }

  const storedMode = window.localStorage.getItem(ideaViewModeStorageKey);

  if (storedMode === 'detailed' || storedMode === 'compact') {
    return storedMode;
  }

  return 'compact';
}

function setViewMode(mode: IdeaViewMode): void {
  viewMode.value = mode;

  if (typeof window !== 'undefined') {
    window.localStorage.setItem(ideaViewModeStorageKey, mode);
  }
}

function viewButtonVariant(mode: IdeaViewMode): 'secondary' | 'ghost' {
  if (viewMode.value === mode) {
    return 'secondary';
  }

  return 'ghost';
}

function tagUrl(tag: string): string {
  const params = new URLSearchParams();

  if (props.keyword) {
    params.set('search', props.keyword);
  }

  params.set('tag', tag);

  return `/ideas?${params.toString()}`;
}

function tagButtonVariant(tag: string): 'secondary' | 'outline' {
  if (props.selectedTag === tag) {
    return 'secondary';
  }

  return 'outline';
}

function scrollToSection(id: string): void {
  document.getElementById(id)?.scrollIntoView({
    behavior: 'smooth',
    block: 'start',
  });
}
</script>

<template>
  <section class="flex flex-col gap-10">
    <div class="flex flex-col gap-4 border-b border-border pb-6">
      <h1 class="sr-only">
        Ideas
      </h1>
      <p class="max-w-2xl text-sm leading-6 text-muted-foreground">
        Browse product ideas, find collaborators, and support work you want to see built.
      </p>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <nav
          class="flex w-full flex-wrap gap-2 sm:w-auto"
          aria-label="Idea discovery sections">
          <Button
            v-if="hasActiveFilters"
            type="button"
            variant="ghost"
            size="sm"
            class="flex-1 rounded-md border border-transparent sm:flex-none"
            @click="scrollToSection('search-results')">
            <SearchX
              class="size-4"
              aria-hidden="true" />
            Search results
          </Button>
          <template v-else>
            <Button
              type="button"
              variant="ghost"
              size="sm"
              class="flex-1 rounded-md border border-transparent sm:flex-none"
              @click="scrollToSection('trending-ideas')">
              <Flame
                class="size-4"
                aria-hidden="true" />
              Trending
            </Button>
            <Button
              type="button"
              variant="ghost"
              size="sm"
              class="flex-1 rounded-md border border-transparent sm:flex-none"
              @click="scrollToSection('ideas')">
              <ListFilter
                class="size-4"
                aria-hidden="true" />
              Recent
            </Button>
          </template>
        </nav>

        <div
          class="flex w-full flex-wrap gap-1 rounded-md border border-border bg-card p-1 sm:w-auto"
          role="group"
          aria-label="Idea card density">
          <Button
            type="button"
            :variant="viewButtonVariant('detailed')"
            size="sm"
            :aria-pressed="viewMode === 'detailed'"
            class="flex-1 sm:flex-none"
            @click="setViewMode('detailed')">
            <Rows3
              class="size-4"
              aria-hidden="true" />
            Detailed
          </Button>
          <Button
            type="button"
            :variant="viewButtonVariant('compact')"
            size="sm"
            :aria-pressed="viewMode === 'compact'"
            class="flex-1 sm:flex-none"
            @click="setViewMode('compact')">
            <LayoutGrid
              class="size-4"
              aria-hidden="true" />
            Compact
          </Button>
        </div>
      </div>

      <div
        v-if="popularTags.length > 0"
        class="flex flex-col gap-2">
        <div class="flex items-center gap-2 text-xs font-medium uppercase text-muted-foreground">
          <Tag
            class="size-3.5"
            aria-hidden="true" />
          Browse by tag
        </div>
        <div class="flex flex-wrap gap-2">
          <Button
            v-for="tag in popularTags"
            :key="tag.name"
            as="a"
            :href="tagUrl(tag.name)"
            :variant="tagButtonVariant(tag.name)"
            size="sm"
            :aria-current="selectedTag === tag.name ? 'true' : undefined">
            {{ tag.name }}
            <span class="text-xs text-muted-foreground">
              {{ tag.count.toLocaleString() }}
            </span>
          </Button>
          <Button
            v-if="selectedTag"
            as="a"
            :href="clearTagUrl"
            variant="ghost"
            size="sm">
            <X
              class="size-4"
              aria-hidden="true" />
            Clear tag
          </Button>
        </div>
      </div>
    </div>

    <div
      v-if="searchResults"
      id="search-results"
      class="flex scroll-mt-24 flex-col gap-4">
      <div class="flex flex-col gap-1">
        <h2 class="text-xl font-semibold text-white">
          {{ resultsHeading }}
        </h2>
        <p class="text-sm text-muted-foreground">
          {{ resultsDescription }}
        </p>
      </div>
      <IdeaList
        v-if="searchResults.items.length > 0"
        :ideas="searchResults.items"
        :variant="ideaListVariant" />
      <div
        v-else
        class="flex flex-col gap-4 rounded-md border border-dashed border-border p-6 text-sm text-muted-foreground">
        <p>
          {{ emptyResultsMessage }}
        </p>
        <div class="flex flex-wrap gap-2">
          <Button
            as="a"
            href="/ideas"
            variant="outline"
            size="sm">
            Clear search
          </Button>
          <Button
            as="a"
            href="/ideas/create"
            size="sm">
            Share an idea
          </Button>
        </div>
      </div>
      <PaginationLinks :paginator="searchResults" />
    </div>

    <template v-else>
      <div
        id="trending-ideas"
        class="flex scroll-mt-24 flex-col gap-4">
        <div class="flex flex-col gap-1">
          <h2 class="text-xl font-semibold text-white">
            Trending ideas
          </h2>
          <p class="text-sm text-muted-foreground">
            Recently active ideas with the strongest support signals.
          </p>
        </div>
        <IdeaList
          v-if="trendingIdeas.length > 0"
          :ideas="trendingIdeas"
          featured
          :variant="ideaListVariant" />
        <div
          v-else
          class="flex flex-col gap-4 rounded-md border border-dashed border-border p-6 text-sm text-muted-foreground">
          <p>
            No ideas are trending yet.
          </p>
          <Button
            as="a"
            href="/ideas/create"
            size="sm"
            class="w-fit">
            Share an idea
          </Button>
        </div>
      </div>

      <div
        id="ideas"
        class="flex scroll-mt-24 flex-col gap-4">
        <div class="flex flex-col gap-1">
          <h2 class="text-xl font-semibold text-white">
            Recent ideas
          </h2>
          <p class="text-sm text-muted-foreground">
            Fresh open ideas not already featured above.
          </p>
        </div>
        <IdeaList
          v-if="recentIdeas.length > 0"
          :ideas="recentIdeas"
          :variant="ideaListVariant" />
        <div
          v-else
          class="flex flex-col gap-4 rounded-md border border-dashed border-border p-6 text-sm text-muted-foreground">
          <p>
            No additional recent ideas are available yet.
          </p>
          <Button
            as="a"
            href="/ideas/create"
            size="sm"
            class="w-fit">
            Share an idea
          </Button>
        </div>
        <PaginationLinks :paginator="ideas" />
      </div>
    </template>
  </section>
</template>
