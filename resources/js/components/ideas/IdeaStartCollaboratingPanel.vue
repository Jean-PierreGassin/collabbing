<script setup lang="ts">
import { computed } from 'vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import MethodField from '@/components/forms/MethodField.vue';
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

const readinessBadges = computed(() => [
  {
    complete: props.idea.collaboration.readinessBadges.applicationsOpen,
    label: props.idea.collaboration.readinessBadges.applicationsOpen ? 'Applications open' : 'Applications closed',
  },
  {
    complete: props.idea.collaboration.readinessBadges.firstStepListed,
    label: props.idea.collaboration.readinessBadges.firstStepListed ? 'First step listed' : 'No first step yet',
  },
  {
    complete: props.idea.collaboration.readinessBadges.repoAvailable,
    label: props.idea.collaboration.readinessBadges.repoAvailable ? 'Repo available' : 'No repo yet',
  },
  {
    complete: props.idea.collaboration.readinessBadges.startNotesReady,
    label: props.idea.collaboration.readinessBadges.startNotesReady ? 'Start notes ready' : 'Start notes pending',
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
      <div class="flex flex-col gap-3">
        <div class="flex items-start justify-between gap-3">
          <div class="flex flex-col gap-1">
            <h2
              id="start-collaborating-heading"
              class="text-lg font-semibold text-white">
              Start Collaborating
            </h2>
            <p class="text-sm text-muted-foreground">
              See what this idea needs and how to take the first useful step.
            </p>
          </div>
          <Badge
            :variant="idea.collaboration.applicationsOpen ? 'default' : 'outline'"
            class="shrink-0">
            {{ applicationStateLabel }}
          </Badge>
        </div>

        <div class="flex flex-wrap gap-2">
          <template
            v-for="badge in readinessBadges"
            :key="badge.label">
            <span
              class="inline-flex items-center gap-1.5 rounded-md border px-2 py-1 text-xs font-medium"
              :class="badge.complete ? 'border-primary/25 bg-primary/14 text-foreground' : 'border-border bg-background/40 text-muted-foreground'">
              <CheckCircle2
                v-if="badge.complete"
                class="size-3.5 text-primary"
                aria-hidden="true" />
              <Circle
                v-else
                class="size-3.5"
                aria-hidden="true" />
              {{ badge.label }}
            </span>
          </template>
        </div>
      </div>
    </CardHeader>

    <CardContent class="flex flex-col gap-5 text-sm">
      <dl class="grid gap-4">
        <div class="flex flex-col gap-1.5">
          <dt class="text-xs font-medium uppercase text-muted-foreground">
            Stage
          </dt>
          <dd class="text-foreground">
            {{ idea.collaboration.stageDisplay }}
          </dd>
        </div>

        <div class="flex flex-col gap-2">
          <dt class="text-xs font-medium uppercase text-muted-foreground">
            Help wanted
          </dt>
          <dd class="flex flex-wrap gap-1.5">
            <Badge
              v-for="label in helpWantedLabels"
              :key="label"
              variant="secondary">
              {{ label }}
            </Badge>
          </dd>
          <p
            v-if="idea.collaboration.helpWantedNote"
            class="text-muted-foreground">
            {{ idea.collaboration.helpWantedNote }}
          </p>
        </div>

        <div class="flex flex-col gap-1.5">
          <dt class="text-xs font-medium uppercase text-muted-foreground">
            First contribution
          </dt>
          <dd
            class="whitespace-pre-line text-foreground"
            :class="{ 'text-muted-foreground': !idea.collaboration.firstContribution }">
            {{ firstContributionLabel }}
          </dd>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
          <div class="flex flex-col gap-1.5">
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

          <div class="flex flex-col gap-1.5">
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
        </div>
      </dl>

      <div
        v-if="!idea.collaboration.applicationsOpen && idea.collaboration.applicationsClosedNote"
        class="rounded-md border border-border bg-background/35 px-3 py-2 text-muted-foreground">
        {{ idea.collaboration.applicationsClosedNote }}
      </div>

      <div class="flex flex-col gap-2 border-t border-border pt-4">
        <Button
          v-if="idea.can.update"
          as="a"
          :href="idea.routes.dashboard"
          size="sm">
          <GitBranch
            class="size-4"
            aria-hidden="true" />
          Manage collaboration
        </Button>
        <Button
          v-else-if="collaborator"
          type="button"
          size="sm"
          disabled>
          <CheckCircle2
            class="size-4"
            aria-hidden="true" />
          Collaborating
        </Button>
        <div
          v-else-if="applicant"
          class="flex flex-col gap-2">
          <span class="inline-flex h-9 items-center justify-center gap-2 rounded-md border border-border bg-background/50 px-3 text-sm font-medium text-foreground">
            <MessageSquare
              class="size-4 text-primary"
              aria-hidden="true" />
            Application pending
          </span>
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
          Apply to Collaborate
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
    </CardContent>
  </Card>
</template>
