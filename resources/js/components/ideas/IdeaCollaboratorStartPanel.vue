<script setup lang="ts">
import { ref } from 'vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { CheckCircle2 } from '@lucide/vue';
import type { Idea, IdeaApplication } from '@/types/domain';

defineProps<{
  idea: Idea;
  collaborator: IdeaApplication;
}>();

const showLeaveForm = ref(false);
</script>

<template>
  <Card
    id="collaborator-start"
    aria-labelledby="collaborator-start-heading">
    <CardHeader>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex min-w-0 flex-col gap-1">
          <h2
            id="collaborator-start-heading"
            class="text-lg font-semibold text-foreground">
            Your starting point
          </h2>
          <p class="text-sm text-muted-foreground">
            Private notes and next steps for accepted collaborators.
          </p>
        </div>
        <Badge
          variant="default"
          class="shrink-0 gap-1.5">
          <CheckCircle2
            class="size-3.5"
            aria-hidden="true" />
          Collaborating
        </Badge>
      </div>
    </CardHeader>

    <CardContent class="flex flex-col gap-4 text-sm">
      <section class="flex flex-col gap-2">
        <h3 class="text-xs font-medium uppercase text-muted-foreground">
          Shared private notes
        </h3>
        <template v-if="idea.collaboration.gettingStartedNotesHtml">
          <MarkdownContent :html="idea.collaboration.gettingStartedNotesHtml" />
          <p
            v-if="idea.collaboration.gettingStartedNotesUpdatedAtForHumans"
            class="text-xs text-muted-foreground">
            Updated {{ idea.collaboration.gettingStartedNotesUpdatedAtForHumans }}.
          </p>
        </template>
        <p
          v-else
          class="text-muted-foreground">
          The owner has not added private start notes yet.
        </p>
      </section>

      <section
        v-if="collaborator.approvalNoteHtml || collaborator.approvalNote"
        class="flex flex-col gap-2 border-t border-border pt-4">
        <h3 class="text-xs font-medium uppercase text-muted-foreground">
          Approval note
        </h3>
        <MarkdownContent
          v-if="collaborator.approvalNoteHtml"
          :html="collaborator.approvalNoteHtml" />
        <p
          v-else
          class="whitespace-pre-line">
          {{ collaborator.approvalNote }}
        </p>
      </section>

      <section class="flex flex-col gap-3 border-t border-border pt-4">
        <div class="flex flex-col gap-1">
          <h3 class="text-xs font-medium uppercase text-muted-foreground">
            Collaboration status
          </h3>
          <p class="text-muted-foreground">
            Leave only if you are no longer actively collaborating on this idea.
          </p>
        </div>

        <Button
          v-if="!showLeaveForm"
          type="button"
          variant="outline"
          size="sm"
          class="w-fit border-destructive/35 text-destructive hover:bg-destructive/10 hover:text-destructive"
          @click="showLeaveForm = true">
          Leave collaboration
        </Button>

        <div
          v-else
          class="rounded-md border border-destructive/25 bg-destructive/10 p-3">
          <form
            :action="collaborator.routes.destroy"
            method="POST"
            class="flex flex-col gap-2">
            <CsrfField />
            <MethodField method="DELETE" />
            <label
              :for="`leave-reason-${collaborator.id}`"
              class="text-xs font-medium uppercase text-muted-foreground">
              Private reason
            </label>
            <textarea
              :id="`leave-reason-${collaborator.id}`"
              name="exit_reason"
              class="min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40"
              maxlength="1200"
              placeholder="Optional note for the owner."
            />
            <div class="flex flex-wrap gap-2">
              <Button
                type="submit"
                variant="destructive"
                size="sm">
                Confirm leave
              </Button>
              <Button
                type="button"
                variant="ghost"
                size="sm"
                @click="showLeaveForm = false">
                Cancel
              </Button>
            </div>
          </form>
        </div>
      </section>
    </CardContent>
  </Card>
</template>
