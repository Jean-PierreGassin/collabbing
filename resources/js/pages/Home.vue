<script setup lang="ts">
import { FlaskConical, MessageCircle, Users } from '@lucide/vue';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();

const features = [
  {
    title: 'Pitch ideas',
    description: 'Show off your ideas to the community and get real-time feedback, interest, and a sense of direction',
    icon: MessageCircle,
  },
  {
    title: 'Be part of a team',
    description: "Find collaborators to work on your idea with you, or apply to other ideas that you're interested in",
    icon: Users,
  },
  {
    title: 'Automate project flows',
    description: "Once your project is ready, don't worry about creating a repository - we've got that sorted with our automatic integrations",
    icon: FlaskConical,
  },
];
</script>

<template>
  <section class="flex min-h-[45vh] flex-col items-center justify-center gap-8 text-center">
    <h1 class="text-5xl font-semibold tracking-normal text-white">
      <b>Collaborate</b> better.
    </h1>
    <Button :as="Link" :href="session.routes.ideas" variant="outline" size="lg">
      Browse Ideas
    </Button>
  </section>

  <section class="grid gap-4 md:grid-cols-3">
    <Card v-for="feature in features" :key="feature.title">
      <CardHeader class="items-center text-center">
        <div class="mb-2 flex size-12 items-center justify-center rounded-md bg-primary/10 text-primary">
          <component :is="feature.icon" class="size-6" aria-hidden="true" />
        </div>
        <CardTitle class="text-primary">{{ feature.title }}</CardTitle>
        <CardDescription>{{ feature.description }}</CardDescription>
      </CardHeader>
    </Card>
  </section>
</template>
