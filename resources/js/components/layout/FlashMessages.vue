<script setup lang="ts">
import { computed } from 'vue';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();
const errors = computed(() => session.flash.errors);
</script>

<template>
  <div v-if="session.flash.status || errors.length > 0" class="flex flex-col gap-3">
    <div v-if="session.flash.status" class="rounded-md border border-primary/30 bg-primary/10 px-4 py-3 text-sm text-primary">
      {{ session.flash.status }}
    </div>
    <div v-if="errors.length > 0" class="rounded-md border border-destructive/40 bg-destructive/10 px-4 py-3 text-sm text-destructive">
      <ul class="list-inside list-disc">
        <li v-for="error in errors" :key="error">{{ error }}</li>
      </ul>
    </div>
  </div>
</template>
