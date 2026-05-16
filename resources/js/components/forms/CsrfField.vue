<script setup lang="ts">
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();
</script>

<template>
  <input v-if="session.csrfToken" type="hidden" name="_token" :value="session.csrfToken">
</template>
