<script setup lang="ts">
import PaginationLinks from '@/components/pagination/PaginationLinks.vue';
import type { DomainUser } from '@/types/domain';
import type { Paginator } from '@/types/domain';

defineProps<{
  users: Paginator<DomainUser>;
}>();
</script>

<template>
  <section class="flex flex-col gap-5">
    <div class="flex flex-col gap-1">
      <h1 class="text-2xl font-semibold text-white">Members</h1>
      <p class="max-w-2xl text-sm leading-6 text-muted-foreground">
        Find people building, supporting, and collaborating on ideas.
      </p>
    </div>

    <div v-if="users.items.length > 0" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
      <article v-for="user in users.items" :key="user.id" class="flex min-w-0 gap-3 rounded-md border border-border bg-card p-4">
        <img class="size-12 rounded-full border border-border object-cover" :src="user.profilePicture" :alt="`${user.name} profile picture`">
        <div class="flex min-w-0 flex-1 flex-col gap-1">
          <a class="truncate font-medium text-white transition-colors hover:text-primary" :href="user.routes.show">
            {{ user.name }}
          </a>
          <span class="truncate text-sm text-muted-foreground">@{{ user.username }}</span>
          <p v-if="user.bio" class="line-clamp-2 text-sm leading-5 text-muted-foreground">
            {{ user.bio }}
          </p>
        </div>
      </article>
    </div>

    <div v-else class="rounded-md border border-dashed border-border p-6 text-sm text-muted-foreground">
      No members are available yet.
    </div>

    <PaginationLinks :paginator="users" />
  </section>
</template>
