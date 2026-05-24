<script setup lang="ts">
import { Clock3, GitBranch, Pencil, Send, UserCheck, Users } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import type { Idea, IdeaApplication } from '@/types/domain';

defineProps<{
  applicant: IdeaApplication | null;
  collaborator: IdeaApplication | null;
  idea: Idea;
}>();

function countLabel(count: number, singular: string, plural: string): string {
  if (count === 1) {
    return `1 ${singular}`;
  }

  return `${count.toLocaleString()} ${plural}`;
}
</script>

<template>
  <div class="flex flex-col gap-3 rounded-md border border-border bg-card/70 p-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex min-w-0 flex-wrap items-center gap-2 text-sm text-muted-foreground">
      <Badge variant="outline">
        {{ idea.statusDisplay }}
      </Badge>
      <span class="inline-flex items-center gap-1.5">
        <Users
          class="size-4 text-primary"
          aria-hidden="true" />
        {{ countLabel(idea.approvedApplicationsCount, 'collaborator', 'collaborators') }}
      </span>
      <span class="inline-flex items-center gap-1.5">
        <UserCheck
          class="size-4 text-primary"
          aria-hidden="true" />
        {{ countLabel(idea.supportersCount, 'supporter', 'supporters') }}
      </span>
    </div>

    <div class="flex shrink-0 flex-wrap gap-2">
      <template v-if="idea.can.update">
        <Button
          as="a"
          :href="idea.routes.dashboard"
          size="sm">
          <GitBranch
            class="size-4"
            aria-hidden="true" />
          Manage
        </Button>
        <Button
          as="a"
          :href="idea.routes.edit"
          variant="outline"
          size="sm">
          <Pencil
            class="size-4"
            aria-hidden="true" />
          Edit
        </Button>
      </template>
      <Button
        v-else-if="collaborator"
        type="button"
        variant="secondary"
        size="sm"
        disabled>
        <UserCheck
          class="size-4"
          aria-hidden="true" />
        Collaborator
      </Button>
      <Button
        v-else-if="applicant"
        type="button"
        variant="secondary"
        size="sm"
        disabled>
        <Clock3
          class="size-4"
          aria-hidden="true" />
        Application pending
      </Button>
      <Button
        v-else-if="idea.can.storeApplication"
        as="a"
        :href="idea.routes.applicationsCreate"
        size="sm">
        <Send
          class="size-4"
          aria-hidden="true" />
        Apply to collaborate
      </Button>
    </div>
  </div>
</template>
