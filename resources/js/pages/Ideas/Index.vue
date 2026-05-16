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
    <div v-if="searchResults" id="search-results" class="flex flex-col gap-4">
      <h3 class="text-2xl font-semibold">Showing all results for "{{ keyword }}" 🔎</h3>
      <IdeaList v-if="searchResults.items.length > 0" :ideas="searchResults.items" />
      <p v-else>Darn it, we couldn't find anything related to "{{ keyword }}"</p>
      <PaginationLinks :paginator="searchResults" />
    </div>

    <template v-else>
      <div id="trending-ideas" class="flex flex-col gap-4">
        <h3 class="text-2xl font-semibold">Trending Ideas 🔥</h3>
        <IdeaList v-if="trendingIdeas.length > 0" :ideas="trendingIdeas" />
        <p v-else>That's unusual, nothing seems to be trending 😔</p>
      </div>

      <div id="ideas" class="flex flex-col gap-4">
        <h3 class="text-2xl font-semibold">Recent Ideas 🕔</h3>
        <IdeaList v-if="ideas.items.length > 0" :ideas="ideas.items" />
        <p v-else>There doesn't seem to be anything here yet...</p>
        <PaginationLinks :paginator="ideas" />
      </div>
    </template>
  </section>
</template>
