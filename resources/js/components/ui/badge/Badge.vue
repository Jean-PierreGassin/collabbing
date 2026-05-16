<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { badgeVariants, type BadgeVariants } from './variants';

const props = defineProps<{
  class?: HTMLAttributes['class'];
  variant?: BadgeVariants['variant'];
}>();
</script>

<template>
  <span :class="cn(badgeVariants({ variant: props.variant }), props.class)">
    <slot />
  </span>
</template>
