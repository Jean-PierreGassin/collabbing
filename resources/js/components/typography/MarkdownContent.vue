<script setup lang="ts">
defineProps<{
  html: string;
}>();
</script>

<template>
  <div class="markdown-content" v-html="html" />
</template>
