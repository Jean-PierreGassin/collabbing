<script setup lang="ts">
import { ArrowRight, BadgeDollarSign, MessagesSquare, Route } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();

const resources = [
  {
    title: 'Feedback',
    description: 'Keep the existing feedback route available while rebuilding the experience as a Vue page.',
    icon: MessagesSquare,
    href: session.routes.feedback,
  },
  {
    title: 'Pricing',
    description: 'Move static product pages into Vue first so Blade retirement can proceed with low risk.',
    icon: BadgeDollarSign,
    href: session.routes.pricing,
  },
  {
    title: 'Routing',
    description: 'Use /app routes for new Vue pages, then replace classic pages once each product area is ready.',
    icon: Route,
    href: session.routes.app,
  },
];
</script>

<template>
  <section class="flex flex-col gap-6">
    <div class="flex max-w-3xl flex-col gap-3">
      <h1 class="text-3xl font-semibold tracking-normal">Resources</h1>
      <p class="text-base leading-7 text-muted-foreground">
        This area can absorb static resource pages first, which is a low-risk way to retire Blade views
        before moving heavier idea and dashboard workflows.
      </p>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
      <Card v-for="resource in resources" :key="resource.title">
        <CardHeader>
          <div class="mb-2 flex size-10 items-center justify-center rounded-md bg-secondary text-secondary-foreground">
            <component :is="resource.icon" class="size-5" aria-hidden="true" />
          </div>
          <CardTitle>{{ resource.title }}</CardTitle>
          <CardDescription>{{ resource.description }}</CardDescription>
          <Button class="mt-3 w-fit" as="a" :href="resource.href" variant="outline">
            Open
            <ArrowRight class="size-4" aria-hidden="true" />
          </Button>
        </CardHeader>
      </Card>
    </div>
  </section>
</template>
