<script setup lang="ts">
import type { DomainUser } from '@/types/domain';

defineProps<{
  users: DomainUser[];
}>();
</script>

<template>
  <section class="flex flex-col gap-3">
    <a v-for="user in users" :key="user.id" class="text-primary hover:underline" :href="user.routes.show">
      @{{ user.username }}
    </a>
  </section>
</template>
