<script setup lang="ts">
import { AlertTriangle, ExternalLink, GitCommit } from '@lucide/vue';
import { Link } from '@inertiajs/vue3';
import PaginationLinks from '@/components/pagination/PaginationLinks.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import type { Idea, RepositoryActivityArchive, RepositoryEvent } from '@/types/domain';

defineProps<{
  idea: Idea;
  archive: RepositoryActivityArchive | null;
}>();

function eventLabel(event: RepositoryEvent): string {
  if (event.type === 'repository_commit') {
    return 'Commit';
  }

  if (event.type === 'repository_synced') {
    return 'Sync';
  }

  if (event.type === 'repository_missing') {
    return 'Missing';
  }

  if (event.type === 'repository_restored') {
    return 'Restored';
  }

  if (event.type === 'repository_branch_changed') {
    return 'Branch';
  }

  if (event.type === 'repository_issues_changed') {
    return 'Issues';
  }

  return 'Activity';
}
</script>

<template>
  <section class="flex flex-col gap-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
      <div class="flex min-w-0 flex-col gap-1">
        <p class="text-sm text-muted-foreground">
          Repository activity
        </p>
        <h1 class="text-2xl font-semibold leading-tight text-white">
          {{ idea.titleDisplay }}
        </h1>
      </div>
      <Button
        :as="Link"
        :href="idea.routes.show"
        variant="outline"
        size="sm">
        Back to idea
      </Button>
    </header>

    <Card>
      <CardHeader>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div class="flex min-w-0 flex-col gap-1">
            <h2 class="text-lg font-semibold text-white">
              Activity history
            </h2>
            <p
              v-if="archive?.repository.name"
              class="break-all text-sm text-muted-foreground">
              {{ archive.repository.name }}
            </p>
          </div>
          <Button
            v-if="archive?.repository.htmlUrl"
            as="a"
            :href="archive.repository.htmlUrl"
            target="_blank"
            rel="noopener noreferrer"
            variant="outline"
            size="sm">
            Open repository
            <ExternalLink
              class="size-4"
              aria-hidden="true" />
          </Button>
        </div>
      </CardHeader>

      <CardContent class="flex flex-col gap-4">
        <div
          v-if="!archive"
          class="rounded-md border border-dashed border-border p-6 text-sm text-muted-foreground">
          No repository has been created for this idea yet.
        </div>

        <template v-else>
          <div
            v-if="archive.repository.isMissing"
            class="flex gap-3 rounded-md border border-destructive/40 bg-destructive/10 px-3 py-3 text-sm text-destructive">
            <AlertTriangle
              class="mt-0.5 size-4 shrink-0"
              aria-hidden="true" />
            <div>
              <p class="font-medium">
                Repository unavailable
              </p>
              <p class="mt-1 text-muted-foreground">
                The activity history is preserved even though the code host no longer reports this repository as available.
              </p>
            </div>
          </div>

          <p
            v-if="archive.repository.lastSyncedAtForHumans"
            class="text-sm text-muted-foreground">
            Last synced {{ archive.repository.lastSyncedAtForHumans }}.
          </p>

          <ol
            v-if="archive.events.items.length > 0"
            class="flex flex-col gap-3"
            aria-label="Repository activity history">
            <li
              v-for="event in archive.events.items"
              :key="event.id"
              class="grid grid-cols-[auto_1fr] gap-3 rounded-md border border-border bg-background/35 p-3">
              <span class="inline-flex h-8 min-w-16 items-center justify-center rounded-full border border-border bg-card px-2 text-xs font-medium text-muted-foreground">
                {{ eventLabel(event) }}
              </span>
              <div class="min-w-0">
                <p class="break-words text-sm text-foreground">
                  {{ event.summary }}
                </p>
                <p class="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                  <GitCommit
                    class="size-3.5"
                    aria-hidden="true" />
                  {{ event.occurredAtForHumans }}
                </p>
              </div>
            </li>
          </ol>

          <div
            v-else
            class="rounded-md border border-dashed border-border p-6 text-sm text-muted-foreground">
            No repository events have been recorded yet.
          </div>

          <PaginationLinks
            :paginator="archive.events"
            label="Repository activity pages" />
        </template>
      </CardContent>
    </Card>
  </section>
</template>
