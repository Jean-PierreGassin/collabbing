<script setup lang="ts">
import IdeaList from '@/components/ideas/IdeaList.vue';
import PaginationLinks from '@/components/pagination/PaginationLinks.vue';
import type { Idea, Paginator } from '@/types/domain';

defineProps<{
  keyword: string | null;
  searchResults: Paginator<Idea> | null;
  trendingIdeas: Idea[];
  ideas: Paginator<Idea>;
}>();
</script>

<template>
  <section class="flex flex-col gap-8">
    <div class="flex flex-col gap-1">
      <h1 class="text-2xl font-semibold text-white">Ideas</h1>
      <p class="max-w-2xl text-sm leading-6 text-muted-foreground">
        Browse product ideas, find collaborators, and support work you want to see built.
      </p>
    </div>

    <div v-if="searchResults" id="search-results" class="flex flex-col gap-4">
      <h2 class="text-xl font-semibold text-white">Results for "{{ keyword }}"</h2>
      <IdeaList v-if="searchResults.items.length > 0" :ideas="searchResults.items" />
      <p v-else>No ideas matched "{{ keyword }}".</p>
      <PaginationLinks :paginator="searchResults" />
    </div>

    <template v-else>
      <div id="trending-ideas" class="flex flex-col gap-4">
        <h2 class="text-xl font-semibold text-white">Trending Ideas</h2>
        <IdeaList v-if="trendingIdeas.length > 0" :ideas="trendingIdeas" />
        <p v-else>No ideas are trending yet.</p>
      </div>

      <div id="ideas" class="flex flex-col gap-4">
        <h2 class="text-xl font-semibold text-white">Recent Ideas</h2>
        <IdeaList v-if="ideas.items.length > 0" :ideas="ideas.items" />
        <p v-else>No ideas have been shared yet.</p>
        <PaginationLinks :paginator="ideas" />
      </div>
    </template>
  </section>
</template>
