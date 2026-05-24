<script setup lang="ts">
import { computed } from 'vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { InfoTooltip } from '@/components/ui/tooltip';
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
const registerActionLabel = computed(() => {
  if (props.idea.collaboration.applicationsOpen) {
    return 'Register to apply';
  }

  return 'Register';
});

const readinessBadges = computed(() => [
  {
    complete: props.idea.collaboration.readinessBadges.applicationsOpen,
    label: 'Applications open',
  },
  {
    complete: props.idea.collaboration.readinessBadges.firstStepListed,
    label: 'First step listed',
  },
  {
    complete: props.idea.collaboration.readinessBadges.repoAvailable,
    label: 'Repo connected',
  },
  {
    complete: props.idea.collaboration.readinessBadges.startNotesReady,
    label: 'Start notes ready',
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
    </CardHeader>

    <CardContent class="flex flex-col gap-4 text-sm">
      <section
        v-if="!idea.can.update"
        class="flex flex-col gap-2 rounded-md border border-primary/20 bg-primary/10 p-3"
        aria-label="Collaboration action">
        <div
          v-if="collaborator"
          class="flex flex-col gap-3">
          <span class="inline-flex w-fit items-center gap-2 rounded-md border border-primary/25 bg-background/45 px-2.5 py-1.5 text-sm font-medium text-foreground">
            <CheckCircle2
              class="size-4 text-primary"
              aria-hidden="true" />
            Collaborating
          </span>
          <Button
            as="a"
            href="#collaborator-start"
            variant="outline"
            size="sm">
            View start notes
          </Button>
        </div>
        <div
          v-else-if="applicant"
          class="flex flex-col gap-2">
          <span class="inline-flex w-fit items-center gap-2 rounded-md border border-primary/25 bg-background/45 px-2.5 py-1.5 text-sm font-medium text-foreground">
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
            {{ registerActionLabel }}
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
        <span
          v-else
          class="inline-flex min-h-9 items-center justify-center gap-2 rounded-md border border-border bg-background/50 px-3 text-sm font-medium text-muted-foreground">
          <Lock
            class="size-4"
            aria-hidden="true" />
          Applications closed
        </span>

        <p class="text-xs leading-5 text-muted-foreground">
          <template v-if="collaborator">
            Your private starting point is part of the main idea flow below.
          </template>
          <template v-else-if="applicant">
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
      </section>

      <section
        :class="[
          'flex flex-col gap-3',
          idea.can.update ? '' : 'border-t border-border pt-4',
        ]">
        <div class="flex flex-col gap-1">
          <h3 class="flex items-center gap-1.5 text-xs font-medium uppercase text-muted-foreground">
            What help is needed
            <InfoTooltip
              id="sidebar-help-wanted-tooltip"
              label="The contribution areas the owner is most interested in right now. Applicants can still propose other useful work."
              align="end" />
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
        </div>

        <div class="flex flex-col gap-1">
          <h3 class="flex items-center gap-1.5 text-xs font-medium uppercase text-muted-foreground">
            First useful step
            <InfoTooltip
              id="sidebar-first-useful-step-tooltip"
              label="A small public starting point so someone can understand the first useful action before they apply."
              align="end" />
          </h3>
          <div
            class="whitespace-pre-line text-foreground"
            :class="{ 'text-muted-foreground': !idea.collaboration.firstContribution }">
            <MarkdownContent
              v-if="idea.collaboration.firstContributionHtml"
              :html="idea.collaboration.firstContributionHtml" />
            <span v-else>{{ firstContributionLabel }}</span>
          </div>
        </div>
      </section>

      <section class="flex flex-col gap-3 border-t border-border pt-4">
        <h3 class="flex items-center gap-1.5 text-xs font-medium uppercase text-muted-foreground">
          Project context
          <InfoTooltip
            id="sidebar-project-context-tooltip"
            label="Public context about coordination style and repository access so applicants can decide whether it fits them."
            align="end" />
        </h3>
        <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
          <div class="flex flex-col gap-1">
            <dt class="text-xs font-medium text-muted-foreground">
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
            <dt class="text-xs font-medium text-muted-foreground">
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
      </section>

      <section class="flex flex-col gap-2 border-t border-border pt-4">
        <h3 class="flex items-center gap-1.5 text-xs font-medium uppercase text-muted-foreground">
          Readiness signals
          <InfoTooltip
            id="sidebar-readiness-signals-tooltip"
            label="A quick checklist of the public and private setup pieces that make collaboration easier to start."
            align="end" />
        </h3>
        <div
          class="flex flex-wrap gap-1.5"
          aria-label="Collaboration readiness signals">
          <Badge
            v-for="badge in readinessBadges"
            :key="badge.label"
            :variant="badge.complete ? 'secondary' : 'outline'"
            :class="[
              'gap-1.5',
              badge.complete ? '' : 'text-muted-foreground',
            ]">
            <CheckCircle2
              v-if="badge.complete"
              class="size-3.5"
              aria-hidden="true" />
            <Circle
              v-else
              class="size-3.5"
              aria-hidden="true" />
            {{ badge.label }}
          </Badge>
        </div>
      </section>

      <div
        v-if="!idea.collaboration.applicationsOpen && idea.collaboration.applicationsClosedNote"
        class="border-t border-border pt-4 text-muted-foreground">
        {{ idea.collaboration.applicationsClosedNote }}
      </div>
    </CardContent>
  </Card>
</template>
