<script setup lang="ts">
import { ChevronDown } from '@lucide/vue';

defineProps<{
  id: string;
  name: string;
}>();
</script>

<template>
  <span class="relative inline-flex min-w-36 items-center">
    <select
      :id="id"
      :name="name"
      class="h-10 w-full appearance-none rounded-md border border-input bg-background/80 py-2 pl-3 pr-9 text-sm text-foreground outline-none transition-colors hover:border-primary/55 focus:border-primary focus:ring-2 focus:ring-ring/40"
    >
      <slot />
    </select>
    <ChevronDown class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-primary" aria-hidden="true" />
  </span>
</template>
