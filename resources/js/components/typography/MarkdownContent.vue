<script setup lang="ts">
import { nextTick, onMounted, onUpdated, ref } from 'vue';

defineProps<{
  html: string;
}>();

const content = ref<HTMLElement | null>(null);

function markExternalLinks(): void {
  content.value?.querySelectorAll<HTMLAnchorElement>('a[href^="http://"], a[href^="https://"]').forEach((link) => {
    if (link.origin === window.location.origin) {
      return;
    }

    link.target = '_blank';
    link.rel = 'noopener noreferrer';
  });
}

onMounted(() => {
  void nextTick(markExternalLinks);
});

onUpdated(() => {
  void nextTick(markExternalLinks);
});
</script>

<template>
  <div
    ref="content"
    class="markdown-content"
    v-html="html" />
</template>
