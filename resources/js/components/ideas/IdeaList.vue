<script setup lang="ts">
import IdeaCard from '@/components/ideas/IdeaCard.vue';
import type { Idea } from '@/types/domain';

defineProps<{
  ideas: Idea[];
}>();
</script>

<template>
  <div class="flex flex-col gap-3">
    <IdeaCard v-for="idea in ideas" :key="idea.id" :idea="idea" />
  </div>
</template>
