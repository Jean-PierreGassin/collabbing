<script setup lang="ts">
defineProps<{
  method: 'PUT' | 'PATCH' | 'DELETE';
}>();
</script>

<template>
  <input type="hidden" name="_method" :value="method">
</template>
