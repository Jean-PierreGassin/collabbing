<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>

<template>
  <div data-slot="card-header" :class="cn('flex flex-col gap-1.5 border-b border-border p-6', props.class)">
    <slot />
  </div>
</template>
