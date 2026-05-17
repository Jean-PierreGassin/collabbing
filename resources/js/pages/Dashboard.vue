<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import IdeaList from '@/components/ideas/IdeaList.vue';
import { Button } from '@/components/ui/button';
import { useSessionStore } from '@/stores/session';
import type { Idea } from '@/types/domain';

defineProps<{
  ideas: Idea[];
  collaborations: Idea[];
}>();

const session = useSessionStore();
const activeTab = ref<'ideas' | 'collaborations'>(new URLSearchParams(window.location.search).has('collaborations') ? 'collaborations' : 'ideas');
</script>

<template>
  <section class="flex flex-col gap-5">
    <div class="flex flex-col gap-1">
      <h1 class="text-2xl font-semibold text-white">Dashboard</h1>
      <p class="max-w-2xl text-sm leading-6 text-muted-foreground">
        Track ideas you own and collaborations you have joined.
      </p>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex flex-wrap gap-1 rounded-md border border-border bg-card p-1">
        <Button :variant="activeTab === 'ideas' ? 'secondary' : 'ghost'" :aria-pressed="activeTab === 'ideas'" @click="activeTab = 'ideas'">My Ideas</Button>
        <Button :variant="activeTab === 'collaborations' ? 'secondary' : 'ghost'" :aria-pressed="activeTab === 'collaborations'" @click="activeTab = 'collaborations'">Ideas I'm collaborating on</Button>
      </div>
      <Button :as="Link" :href="session.routes.ideasCreate" size="sm">Create an Idea</Button>
    </div>

    <div v-if="activeTab === 'ideas'">
      <IdeaList v-if="ideas.length > 0" :ideas="ideas" />
      <p v-else>You have not shared any ideas yet. <Link class="text-primary hover:underline" :href="session.routes.ideasCreate">Create one</Link></p>
    </div>

    <div v-else>
      <IdeaList v-if="collaborations.length > 0" :ideas="collaborations" />
      <p v-else>You are not collaborating on any ideas yet. <Link class="text-primary hover:underline" :href="session.routes.ideas">Browse ideas</Link></p>
    </div>
  </section>
</template>
