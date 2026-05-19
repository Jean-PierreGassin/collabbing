<script setup lang="ts">
import IdeaSidebar from '@/components/ideas/IdeaSidebar.vue';
import PaginationLinks from '@/components/pagination/PaginationLinks.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, GitBranch } from '@lucide/vue';
import type { Idea, Paginator, RepositoryEvent } from '@/types/domain';

defineProps<{
  idea: Idea;
  events: Paginator<RepositoryEvent>;
}>();
</script>

<template>
  <section class="flex flex-col gap-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
      <div class="flex min-w-0 flex-col gap-1">
        <Button :as="Link" :href="idea.routes.show" variant="ghost" size="sm" class="mb-1 w-fit">
          <ArrowLeft class="size-4" aria-hidden="true" />
          Back to idea
        </Button>
        <h1 class="text-2xl font-semibold leading-tight text-white">Repository activity</h1>
        <p class="text-sm text-muted-foreground">{{ idea.titleDisplay }}</p>
      </div>
      <Button
        v-if="idea.repositoryActivity.htmlUrl"
        as="a"
        :href="idea.repositoryActivity.htmlUrl"
        target="_blank"
        rel="noopener noreferrer"
        variant="outline"
        size="sm"
      >
        <GitBranch class="size-4" aria-hidden="true" />
        View repository
      </Button>
    </header>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
      <Card>
        <CardHeader>
          <div class="flex flex-col gap-1">
            <h2 class="text-lg font-semibold text-white">Activity archive</h2>
            <p class="text-sm text-muted-foreground">A fuller history of synced repository changes for this idea.</p>
          </div>
        </CardHeader>
        <CardContent>
          <ol v-if="events.items.length > 0" class="flex flex-col gap-3" aria-label="Repository activity events">
            <li v-for="event in events.items" :key="event.id" class="rounded-md border border-border bg-background/35 p-4">
              <div class="flex flex-col gap-1">
                <span class="text-sm font-medium text-white">{{ event.summary }}</span>
                <span class="text-xs text-muted-foreground">{{ event.occurredAtForHumans }}</span>
              </div>
            </li>
          </ol>
          <p v-else class="rounded-md border border-dashed border-border p-6 text-sm text-muted-foreground">
            Repository activity will appear here after the repository is created and synced.
          </p>
          <PaginationLinks :paginator="events" :only="['events']" label="Repository activity pages" />
        </CardContent>
      </Card>

      <aside class="flex flex-col gap-3">
        <IdeaSidebar :idea="idea" />
      </aside>
    </div>
  </section>
</template>
