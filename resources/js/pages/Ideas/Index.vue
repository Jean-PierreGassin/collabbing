<script setup lang="ts">
import { computed } from 'vue';
import IdeaList from '@/components/ideas/IdeaList.vue';
import PaginationLinks from '@/components/pagination/PaginationLinks.vue';
import type { Idea, Paginator } from '@/types/domain';

const props = defineProps<{
  keyword: string | null;
  searchResults: Paginator<Idea> | null;
  trendingIdeas: Idea[];
  ideas: Paginator<Idea>;
}>();

const trendingIdeaIds = computed(() => new Set(props.trendingIdeas.map((idea) => idea.id)));
const recentIdeas = computed(() => props.ideas.items.filter((idea) => !trendingIdeaIds.value.has(idea.id)));
</script>

<template>
  <section class="flex flex-col gap-10">
    <div class="flex flex-col gap-3 border-b border-border pb-6">
      <h1 class="sr-only">Ideas</h1>
      <p class="max-w-2xl text-sm leading-6 text-muted-foreground">
        Browse product ideas, find collaborators, and support work you want to see built.
      </p>
    </div>

    <div v-if="searchResults" id="search-results" class="flex flex-col gap-4">
      <div class="flex flex-col gap-1">
        <h2 class="text-xl font-semibold text-white">Results for "{{ keyword }}"</h2>
        <p class="text-sm text-muted-foreground">Open ideas matching your search, newest first.</p>
      </div>
      <IdeaList v-if="searchResults.items.length > 0" :ideas="searchResults.items" />
      <p v-else class="rounded-md border border-dashed border-border p-6 text-sm text-muted-foreground">No ideas matched "{{ keyword }}".</p>
      <PaginationLinks :paginator="searchResults" />
    </div>

    <template v-else>
      <div id="trending-ideas" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1">
          <h2 class="text-xl font-semibold text-white">Trending ideas</h2>
          <p class="text-sm text-muted-foreground">Recently active ideas with the strongest support signals.</p>
        </div>
        <IdeaList v-if="trendingIdeas.length > 0" :ideas="trendingIdeas" featured />
        <p v-else class="rounded-md border border-dashed border-border p-6 text-sm text-muted-foreground">No ideas are trending yet.</p>
      </div>

      <div id="ideas" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1">
          <h2 class="text-xl font-semibold text-white">Recent ideas</h2>
          <p class="text-sm text-muted-foreground">Fresh open ideas not already featured above.</p>
        </div>
        <IdeaList v-if="recentIdeas.length > 0" :ideas="recentIdeas" />
        <p v-else class="rounded-md border border-dashed border-border p-6 text-sm text-muted-foreground">No additional recent ideas are available yet.</p>
        <PaginationLinks :paginator="ideas" />
      </div>
    </template>
  </section>
</template>
