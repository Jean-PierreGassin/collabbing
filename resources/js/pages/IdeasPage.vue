<script setup lang="ts">
import { ArrowRight, CirclePlus, MessageSquare, Star } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();

const ideaFlows = [
  {
    title: 'Discover',
    description: 'A Vue-owned ideas index can replace the Blade list with filters, saved state, and fast navigation.',
    icon: Star,
  },
  {
    title: 'Discuss',
    description: 'Comments and supporter intent can move into reusable feature components instead of Blade includes.',
    icon: MessageSquare,
  },
  {
    title: 'Create',
    description: 'The add/edit flow can become a typed form backed by API requests and composable validation.',
    icon: CirclePlus,
  },
];
</script>

<template>
  <section class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
      <div class="flex max-w-3xl flex-col gap-3">
        <h1 class="text-3xl font-semibold tracking-normal">Ideas</h1>
        <p class="text-base leading-7 text-muted-foreground">
          The first migration seam is ready: keep Laravel serving data and move the idea browsing,
          discussion, and creation experiences into Vue feature slices.
        </p>
      </div>
      <Button as="a" :href="session.routes.classicIdeas" variant="outline">
        Open current ideas
        <ArrowRight class="size-4" aria-hidden="true" />
      </Button>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
      <Card v-for="flow in ideaFlows" :key="flow.title">
        <CardHeader>
          <div class="mb-2 flex size-10 items-center justify-center rounded-md bg-secondary text-secondary-foreground">
            <component :is="flow.icon" class="size-5" aria-hidden="true" />
          </div>
          <CardTitle>{{ flow.title }}</CardTitle>
          <CardDescription>{{ flow.description }}</CardDescription>
        </CardHeader>
      </Card>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Recommended namespace</CardTitle>
        <CardDescription>When each Blade page is migrated, keep feature code grouped by product area.</CardDescription>
      </CardHeader>
      <CardContent>
        <pre class="overflow-x-auto rounded-md bg-muted p-4 text-sm text-muted-foreground"><code>resources/js/features/ideas/
  api/
  components/
  pages/
  stores/
  types.ts</code></pre>
      </CardContent>
    </Card>
  </section>
</template>
