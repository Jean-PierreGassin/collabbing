<script setup lang="ts">
import { ArrowRight, CheckCircle2, GitBranch, Users } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();

const metrics = [
  { label: 'Reusable namespaces', value: '5', icon: CheckCircle2 },
  { label: 'Current auth source', value: 'Laravel', icon: Users },
  { label: 'Classic bridge routes', value: 'Ready', icon: GitBranch },
];
</script>

<template>
  <section class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
      <div class="flex max-w-3xl flex-col gap-3">
        <h1 class="text-3xl font-semibold tracking-normal">Dashboard</h1>
        <p class="text-base leading-7 text-muted-foreground">
          A Vue dashboard shell is ready for authenticated workflows. The session store reads Laravel-provided
          user context, so backend auth can stay authoritative while the interface moves forward.
        </p>
      </div>
      <Button as="a" :href="session.routes.classicDashboard" variant="outline">
        Open current dashboard
        <ArrowRight class="size-4" aria-hidden="true" />
      </Button>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
      <Card v-for="metric in metrics" :key="metric.label">
        <CardHeader class="flex-row items-center justify-between gap-4 space-y-0">
          <div>
            <CardDescription>{{ metric.label }}</CardDescription>
            <CardTitle class="mt-2 text-2xl">{{ metric.value }}</CardTitle>
          </div>
          <span class="flex size-10 items-center justify-center rounded-md bg-accent text-accent-foreground">
            <component :is="metric.icon" class="size-5" aria-hidden="true" />
          </span>
        </CardHeader>
      </Card>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>State boundary</CardTitle>
        <CardDescription>Use Pinia stores for client state that survives route changes.</CardDescription>
      </CardHeader>
      <CardContent>
        <div class="rounded-md border bg-muted/40 p-4 text-sm text-muted-foreground">
          <p>
            Current user:
            <span class="font-medium text-foreground">
              {{ session.user?.name ?? 'Guest' }}
            </span>
          </p>
          <p class="mt-2">
            Authenticated:
            <span class="font-medium text-foreground">
              {{ session.isAuthenticated ? 'Yes' : 'No' }}
            </span>
          </p>
        </div>
      </CardContent>
    </Card>
  </section>
</template>
