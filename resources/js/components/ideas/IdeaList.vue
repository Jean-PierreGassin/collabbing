<script setup lang="ts">
import IdeaCard from '@/components/ideas/IdeaCard.vue';
import type { Idea } from '@/types/domain';

withDefaults(defineProps<{
  ideas: Idea[];
  featured?: boolean;
  variant?: 'compact' | 'detailed';
}>(), {
  featured: false,
  variant: 'compact',
});
</script>

<template>
  <div :class="variant === 'detailed' ? 'flex flex-col gap-4' : 'grid gap-4 md:grid-cols-2 xl:grid-cols-3'">
    <IdeaCard
      v-for="idea in ideas"
      :key="idea.id"
      :idea="idea"
      :featured="featured"
      :variant="variant" />
  </div>
</template>
