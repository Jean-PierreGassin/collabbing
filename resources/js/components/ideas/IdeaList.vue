<script setup lang="ts">
import IdeaCard from '@/components/ideas/IdeaCard.vue';
import type { Idea } from '@/types/domain';

withDefaults(defineProps<{
  ideas: Idea[];
  featured?: boolean;
  variant?: 'default' | 'compact';
}>(), {
  featured: false,
  variant: 'default',
});
</script>

<template>
  <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    <IdeaCard v-for="idea in ideas" :key="idea.id" :idea="idea" :featured="featured" :variant="variant" />
  </div>
</template>
