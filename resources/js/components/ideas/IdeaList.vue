<script setup lang="ts">
import IdeaCard from '@/components/ideas/IdeaCard.vue';
import type { Idea } from '@/types/domain';

withDefaults(defineProps<{
  ideas: Idea[];
  featured?: boolean;
}>(), {
  featured: false,
});
</script>

<template>
  <div :class="featured ? 'grid gap-4 lg:grid-cols-3' : 'flex flex-col gap-3'">
    <IdeaCard v-for="idea in ideas" :key="idea.id" :idea="idea" :featured="featured" />
  </div>
</template>
