<script setup lang="ts">
import { ArrowRight, GitBranch, Lightbulb, Users } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();

const highlights = [
  {
    title: 'Shape ideas',
    description: 'Collect rough concepts, turn them into collaboration-ready proposals, and keep the signal visible.',
    icon: Lightbulb,
  },
  {
    title: 'Find collaborators',
    description: 'Give builders a shared place to apply, support, discuss, and move promising work forward.',
    icon: Users,
  },
  {
    title: 'Connect delivery',
    description: 'Keep repository and collaborator workflows close to the product surface as the app migrates.',
    icon: GitBranch,
  },
];
</script>

<template>
  <section class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
    <div class="flex flex-col gap-6">
      <Badge variant="secondary" class="w-fit">Vue migration workspace</Badge>
      <div class="flex flex-col gap-4">
        <h1 class="max-w-3xl text-4xl font-semibold tracking-normal text-foreground sm:text-5xl">
          Collaborate on ideas without losing the thread.
        </h1>
        <p class="max-w-2xl text-base leading-7 text-muted-foreground">
          This is the new Vue application shell for {{ session.appName }}. It gives the product a modern,
          component-driven surface while the existing Laravel routes can migrate page by page.
        </p>
      </div>
      <div class="flex flex-wrap gap-3">
        <Button as="a" :href="session.routes.classicIdeas">
          Browse current ideas
          <ArrowRight class="size-4" aria-hidden="true" />
        </Button>
        <Button variant="outline" as="a" :href="session.routes.feedback">Send feedback</Button>
      </div>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Migration foundation</CardTitle>
        <CardDescription>Reusable Vue areas are ready for real feature slices.</CardDescription>
      </CardHeader>
      <CardContent>
        <dl class="grid gap-4 sm:grid-cols-2">
          <div class="rounded-md border bg-muted/40 p-4">
            <dt class="text-sm text-muted-foreground">Framework</dt>
            <dd class="mt-1 text-xl font-semibold">Vue 3</dd>
          </div>
          <div class="rounded-md border bg-muted/40 p-4">
            <dt class="text-sm text-muted-foreground">State</dt>
            <dd class="mt-1 text-xl font-semibold">Pinia</dd>
          </div>
          <div class="rounded-md border bg-muted/40 p-4">
            <dt class="text-sm text-muted-foreground">Routing</dt>
            <dd class="mt-1 text-xl font-semibold">Vue Router</dd>
          </div>
          <div class="rounded-md border bg-muted/40 p-4">
            <dt class="text-sm text-muted-foreground">UI</dt>
            <dd class="mt-1 text-xl font-semibold">shadcn-vue</dd>
          </div>
        </dl>
      </CardContent>
    </Card>
  </section>

  <section class="mt-10 grid gap-4 md:grid-cols-3">
    <Card v-for="highlight in highlights" :key="highlight.title">
      <CardHeader>
        <div class="mb-2 flex size-10 items-center justify-center rounded-md bg-accent text-accent-foreground">
          <component :is="highlight.icon" class="size-5" aria-hidden="true" />
        </div>
        <CardTitle>{{ highlight.title }}</CardTitle>
        <CardDescription>{{ highlight.description }}</CardDescription>
      </CardHeader>
    </Card>
  </section>
</template>
