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
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex flex-wrap gap-2">
        <Button :variant="activeTab === 'ideas' ? 'default' : 'ghost'" @click="activeTab = 'ideas'">My Ideas</Button>
        <Button :variant="activeTab === 'collaborations' ? 'default' : 'ghost'" @click="activeTab = 'collaborations'">Ideas I'm collaborating on</Button>
      </div>
      <Button :as="Link" :href="session.routes.ideasCreate" variant="outline" size="sm">Create an Idea</Button>
    </div>

    <div v-if="activeTab === 'ideas'">
      <IdeaList v-if="ideas.length > 0" :ideas="ideas" />
      <p v-else>You're fresh out, why not <Link class="text-primary hover:underline" :href="session.routes.ideasCreate">create one?</Link></p>
    </div>

    <div v-else>
      <IdeaList v-if="collaborations.length > 0" :ideas="collaborations" />
      <p v-else>Don't be shy, <Link class="text-primary hover:underline" :href="session.routes.ideas">start applying!</Link></p>
    </div>
  </section>
</template>
