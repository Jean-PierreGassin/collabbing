<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  status: 401 | 403 | 404 | 500;
}>();

const message = computed(() => {
  if (props.status === 404) {
    return 'Are you sure about that?';
  }

  if (props.status === 500) {
    return 'You broke it!';
  }

  return 'Halt! Who goes there?';
});
</script>

<template>
  <section class="flex min-h-[45vh] flex-col items-center justify-center gap-6 text-center">
    <div class="text-6xl font-semibold">{{ status }}</div>
    <div class="text-4xl font-semibold">{{ message }}</div>
  </section>
</template>
