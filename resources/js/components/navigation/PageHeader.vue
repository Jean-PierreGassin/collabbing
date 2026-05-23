<script setup lang="ts">
defineProps<{
  description?: string | null;
  title: string;
}>();
</script>

<template>
  <header class="flex flex-col gap-1">
    <div class="flex min-w-0 flex-col gap-1">
      <h1 class="text-2xl font-semibold leading-tight text-white">
        {{ title }}
      </h1>
      <p
        v-if="description"
        class="max-w-2xl text-sm leading-6 text-muted-foreground">
        {{ description }}
      </p>
    </div>
  </header>
</template>
