<script setup lang="ts">
import { computed, ref } from 'vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { useSessionStore } from '@/stores/session';
import { CheckCircle2, Circle, GitBranch, Lock, MessageSquare, UserPlus } from '@lucide/vue';
import type { Idea, IdeaApplication } from '@/types/domain';

const props = defineProps<{
  idea: Idea;
  collaborator?: IdeaApplication | null;
  applicant?: IdeaApplication | null;
}>();

const session = useSessionStore();
const showLeaveForm = ref(false);

const helpWantedLabels = computed(() => {
  if (props.idea.collaboration.helpWantedDisplay.length === 0) {
    return ['Open to figuring it out'];
  }

  return props.idea.collaboration.helpWantedDisplay;
});

const applicationStateLabel = computed(() => {
  if (props.idea.collaboration.applicationsOpen) {
    return 'Applications open';
  }

  return 'Applications closed';
});

const firstContributionLabel = computed(() => {
  if (props.idea.collaboration.firstContribution) {
    return props.idea.collaboration.firstContribution;
  }

  return 'No public first step yet.';
});

const repositoryLabel = computed(() => {
  if (props.idea.repository) {
    return 'Repository available';
  }

  return 'No repository connected yet';
});

const registerRoute = computed(() => routeWithNext(session.routes.register));
const loginRoute = computed(() => routeWithNext(session.routes.login));

const readinessItems = computed(() => [
  {
    complete: props.idea.collaboration.readinessBadges.applicationsOpen,
    label: props.idea.collaboration.readinessBadges.applicationsOpen ? 'Open to applications' : 'Applications paused',
  },
  {
    complete: props.idea.collaboration.readinessBadges.firstStepListed,
    label: props.idea.collaboration.readinessBadges.firstStepListed ? 'First step ready' : 'First step missing',
  },
  {
    complete: props.idea.collaboration.readinessBadges.repoAvailable,
    label: props.idea.collaboration.readinessBadges.repoAvailable ? 'Repository ready' : 'Repository later',
  },
  {
    complete: props.idea.collaboration.readinessBadges.startNotesReady,
    label: props.idea.collaboration.readinessBadges.startNotesReady ? 'Private notes ready' : 'Private notes pending',
  },
]);

function routeWithNext(route: string): string {
  const separator = route.includes('?') ? '&' : '?';

  return `${route}${separator}next=${encodeURIComponent(props.idea.routes.show)}`;
}

function confirmWithdraw(event: SubmitEvent): void {
  if (!window.confirm || window.confirm('Withdraw this application? You can apply again later.')) {
    return;
  }

  event.preventDefault();
}
</script>

<template>
  <Card aria-labelledby="start-collaborating-heading">
    <CardHeader>
      <div class="flex items-start justify-between gap-3">
        <div class="flex flex-col gap-1">
          <h2
            id="start-collaborating-heading"
            class="text-lg font-semibold text-foreground">
            Start collaborating
          </h2>
          <p class="text-sm text-muted-foreground">
            What this idea needs, what to do first, and how to join.
          </p>
        </div>
        <Badge
          :variant="idea.collaboration.applicationsOpen ? 'default' : 'outline'"
          class="shrink-0">
          {{ applicationStateLabel }}
        </Badge>
      </div>
    </CardHeader>

    <CardContent class="flex flex-col gap-5 text-sm">
      <section class="flex flex-col gap-2 rounded-md border border-border bg-background/30 p-3">
        <h3 class="text-xs font-medium uppercase text-muted-foreground">
          First useful step
        </h3>
        <div
          class="whitespace-pre-line text-foreground"
          :class="{ 'text-muted-foreground': !idea.collaboration.firstContribution }">
          <MarkdownContent
            v-if="idea.collaboration.firstContributionHtml"
            :html="idea.collaboration.firstContributionHtml" />
          <span v-else>{{ firstContributionLabel }}</span>
        </div>
      </section>

      <section class="flex flex-col gap-2">
        <h3 class="text-xs font-medium uppercase text-muted-foreground">
          Help wanted
        </h3>
        <div class="flex flex-wrap gap-1.5">
          <Badge
            v-for="label in helpWantedLabels"
            :key="label"
            variant="secondary">
            {{ label }}
          </Badge>
        </div>
        <p
          v-if="idea.collaboration.helpWantedNote"
          class="text-muted-foreground">
          {{ idea.collaboration.helpWantedNote }}
        </p>
      </section>

      <dl class="grid gap-3 rounded-md border border-border bg-background/30 p-3 sm:grid-cols-2 lg:grid-cols-1">
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-muted-foreground">
            Stage
          </dt>
          <dd class="text-foreground">
            {{ idea.collaboration.stageDisplay }}
          </dd>
        </div>

        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-muted-foreground">
            Communication
          </dt>
          <dd class="text-foreground">
            {{ idea.collaboration.communicationStyleDisplay }}
          </dd>
          <p
            v-if="idea.collaboration.communicationNote"
            class="text-muted-foreground">
            {{ idea.collaboration.communicationNote }}
          </p>
        </div>

        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-muted-foreground">
            Repository
          </dt>
          <dd class="inline-flex items-center gap-2 text-foreground">
            <GitBranch
              class="size-4 text-primary"
              aria-hidden="true" />
            {{ repositoryLabel }}
          </dd>
        </div>
      </dl>

      <section class="flex flex-col gap-3 rounded-md border border-border bg-background/30 p-3">
        <div class="flex flex-col gap-1">
          <h3 class="text-xs font-medium uppercase text-muted-foreground">
            Readiness
          </h3>
          <p class="text-xs leading-5 text-muted-foreground">
            Signals that help someone understand how ready this idea is for collaboration.
          </p>
        </div>
        <ul class="grid gap-2">
          <li
            v-for="item in readinessItems"
            :key="item.label"
            class="flex gap-2">
            <CheckCircle2
              v-if="item.complete"
              class="mt-0.5 size-4 shrink-0 text-primary"
              aria-hidden="true" />
            <Circle
              v-else
              class="mt-0.5 size-4 shrink-0 text-muted-foreground"
              aria-hidden="true" />
            <span class="flex min-w-0 flex-col gap-0.5">
              <span :class="item.complete ? 'text-foreground' : 'text-muted-foreground'">{{ item.label }}</span>
            </span>
          </li>
        </ul>
      </section>

      <div
        v-if="!idea.collaboration.applicationsOpen && idea.collaboration.applicationsClosedNote"
        class="rounded-md border border-border bg-background/35 px-3 py-2 text-muted-foreground">
        {{ idea.collaboration.applicationsClosedNote }}
      </div>

      <div class="flex flex-col gap-2 border-t border-border pt-4">
        <span
          v-if="idea.can.update"
          class="inline-flex h-9 items-center justify-center gap-2 rounded-md border border-primary/25 bg-primary/10 px-3 text-sm font-medium text-foreground">
          <GitBranch
            class="size-4"
            aria-hidden="true" />
          You own this idea
        </span>
        <div
          v-else-if="collaborator"
          class="flex flex-col gap-3">
          <span class="inline-flex h-9 items-center justify-center gap-2 rounded-md border border-primary/25 bg-primary/10 px-3 text-sm font-medium text-foreground">
            <CheckCircle2
              class="size-4 text-primary"
              aria-hidden="true" />
            Collaborating
          </span>
          <Button
            v-if="!showLeaveForm"
            type="button"
            variant="outline"
            size="sm"
            @click="showLeaveForm = true">
            Leave collaboration
          </Button>
          <div
            v-else
            class="rounded-md border border-border bg-background/35 p-3">
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
              <Button
                type="submit"
                variant="outline"
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
            </form>
          </div>
        </div>
        <div
          v-else-if="applicant"
          class="flex flex-col gap-2">
          <span class="inline-flex h-9 items-center justify-center gap-2 rounded-md border border-border bg-background/50 px-3 text-sm font-medium text-foreground">
            <MessageSquare
              class="size-4 text-primary"
              aria-hidden="true" />
            Application pending
          </span>
          <a
            v-if="applicant.thread"
            href="#application-thread"
            class="inline-flex min-h-9 items-center justify-center gap-2 rounded-md border border-border bg-background/50 px-3 text-sm font-medium text-foreground hover:border-primary/50 hover:text-primary">
            <MessageSquare
              class="size-4 text-primary"
              aria-hidden="true" />
            Application thread
            <span
              v-if="applicant.thread.hasUnread"
              class="rounded-md bg-primary px-1.5 py-0.5 text-xs font-semibold text-primary-foreground">
              {{ applicant.thread.unreadCount.toLocaleString() }} new
            </span>
          </a>
          <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
            <Button
              as="a"
              :href="applicant.routes.edit"
              variant="outline"
              size="sm">
              Edit application
            </Button>
            <form
              :action="applicant.routes.destroy"
              method="POST"
              @submit="confirmWithdraw">
              <CsrfField />
              <MethodField method="DELETE" />
              <Button
                type="submit"
                variant="outline"
                size="sm"
                class="w-full">
                Withdraw
              </Button>
            </form>
          </div>
        </div>
        <div
          v-else-if="!session.isAuthenticated"
          class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
          <Button
            as="a"
            :href="registerRoute"
            size="sm">
            <UserPlus
              class="size-4"
              aria-hidden="true" />
            Register
          </Button>
          <Button
            as="a"
            :href="loginRoute"
            variant="outline"
            size="sm">
            Sign in
          </Button>
        </div>
        <Button
          v-else-if="idea.can.storeApplication"
          as="a"
          :href="idea.routes.applicationsCreate"
          size="sm">
          <UserPlus
            class="size-4"
            aria-hidden="true" />
          Apply to collaborate
        </Button>
        <Button
          v-else
          type="button"
          size="sm"
          disabled>
          <Lock
            class="size-4"
            aria-hidden="true" />
          Applications closed
        </Button>

        <p class="text-xs leading-5 text-muted-foreground">
          <template v-if="applicant">
            You can edit while the owner reviews. Public first steps, support, and comments stay available.
          </template>
          <template v-else-if="!session.isAuthenticated && idea.collaboration.applicationsOpen">
            Sign in to apply, support, or comment.
          </template>
          <template v-else-if="!session.isAuthenticated">
            Sign in to support or comment while applications are closed.
          </template>
          <template v-else-if="idea.collaboration.applicationsOpen">
            Applications stay lightweight; you can still support or comment first.
          </template>
          <template v-else>
            Support and comments remain open while applications are closed.
          </template>
        </p>
      </div>

      <div
        v-if="collaborator"
        class="flex flex-col gap-3 rounded-md border border-primary/25 bg-primary/10 p-3">
        <div class="flex flex-col gap-1">
          <h3 class="text-sm font-semibold text-foreground">
            Your starting point
          </h3>
          <p class="text-xs leading-5 text-muted-foreground">
            Private guidance appears here after approval.
          </p>
        </div>

        <div class="flex flex-col gap-2">
          <span class="text-xs font-medium uppercase text-muted-foreground">
            Shared private notes
          </span>
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
        </div>

        <div
          v-if="collaborator.approvalNoteHtml || collaborator.approvalNote"
          class="flex flex-col gap-2 border-t border-primary/20 pt-3">
          <span class="text-xs font-medium uppercase text-muted-foreground">
            Approval note
          </span>
          <MarkdownContent
            v-if="collaborator.approvalNoteHtml"
            :html="collaborator.approvalNoteHtml" />
          <p
            v-else
            class="whitespace-pre-line">
            {{ collaborator.approvalNote }}
          </p>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
