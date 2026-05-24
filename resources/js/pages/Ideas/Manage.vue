<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import IdeaApplicationThread from '@/components/ideas/IdeaApplicationThread.vue';
import PaginationLinks from '@/components/pagination/PaginationLinks.vue';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { useAccessibleTabs, type AccessibleTab } from '@/lib/tabs';
import { Link, usePage } from '@inertiajs/vue3';
import { Check, Circle, GitBranch, Pencil } from '@lucide/vue';
import { computed } from 'vue';
import type { SharedPageProps } from '@/types/app';
import type { Idea, IdeaApplication, Paginator } from '@/types/domain';

type ManageTab = 'applications' | 'collaborators';

const props = defineProps<{
  idea: Idea;
  applications: Paginator<IdeaApplication>;
  collaborators: Paginator<IdeaApplication>;
}>();

const page = usePage<SharedPageProps>();
const showRepositoryInvitePrompt = computed(() => page.props.flash.repositoryInvitePrompt && props.idea.repository);
const showRepositoryAccessPrompt = computed(() => (
  page.props.flash.repositoryAccessPrompt || props.idea.repositoryAccessReviewNeeded === true
) && props.idea.repository);
const canInviteCollaborators = computed(() => props.idea.repository && props.idea.user.hasGithubToken);
const repositoryInvitePromptText = computed(() => {
  if (canInviteCollaborators.value) {
    return 'Repository is connected. Invite approved collaborators when you are ready.';
  }

  return 'Repository is connected. Connect your code host to invite approved collaborators.';
});
const readinessItems = computed(() => [
  {
    actionHref: props.idea.routes.edit,
    actionLabel: 'Edit',
    complete: props.idea.collaboration.stage !== null,
    label: 'Set where the idea is at',
  },
  {
    actionHref: props.idea.routes.edit,
    actionLabel: 'Edit',
    complete: props.idea.collaboration.helpWanted.length > 0 || props.idea.collaboration.helpWantedNote !== null,
    label: 'Choose the help you want',
  },
  {
    actionHref: props.idea.routes.edit,
    actionLabel: 'Edit',
    complete: props.idea.collaboration.firstContribution !== null,
    label: 'Add a first thing someone can do',
  },
  {
    actionHref: props.idea.routes.edit,
    actionLabel: 'Edit',
    complete: props.idea.collaboration.communicationStyle !== null,
    label: 'Choose how you prefer to coordinate',
  },
  {
    actionHref: null,
    actionLabel: null,
    complete: true,
    label: 'Decide whether applications are open',
  },
  {
    actionHref: props.idea.routes.edit,
    actionLabel: 'Edit',
    complete: props.idea.collaboration.gettingStartedNotesReady,
    label: 'Write private start notes for accepted collaborators',
  },
  {
    actionHref: props.idea.routes.repositoryCreate,
    actionLabel: props.idea.user.hasGithubToken ? 'Create' : 'Connect',
    complete: props.idea.repository,
    label: 'Create or connect a repository',
  },
]);

const manageTabs = [
  {
    value: 'applications',
    label: 'Applications',
    tabId: 'manage-applications-tab',
    panelId: 'manage-applications-panel',
  },
  {
    value: 'collaborators',
    label: 'Collaborators',
    tabId: 'manage-collaborators-tab',
    panelId: 'manage-collaborators-panel',
  },
] satisfies ReadonlyArray<AccessibleTab<ManageTab> & { label: string }>;

function initialTab(): ManageTab {
  if (new URLSearchParams(window.location.search).has('collaborators')) {
    return 'collaborators';
  }

  return 'applications';
}

function tabVariant(tab: ManageTab): 'ghost' | 'secondary' {
  if (activeTab.value === tab) {
    return 'secondary';
  }

  return 'ghost';
}

const {
  activeTab,
  handleTabKeydown,
  isSelected,
  selectTab,
  tabIndex,
} = useAccessibleTabs(manageTabs, initialTab());
</script>

<template>
  <section class="flex flex-col gap-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
      <div class="flex min-w-0 flex-col gap-1">
        <h1 class="text-2xl font-semibold leading-tight text-white">
          {{ idea.title }}
        </h1>
      </div>
      <div class="flex shrink-0 flex-wrap justify-end gap-2 sm:pt-0.5">
        <Button
          v-if="!idea.repository && !idea.user.hasGithubToken"
          as="a"
          :href="idea.routes.repositoryCreate"
          size="sm">
          <GitBranch
            class="size-4"
            aria-hidden="true" />
          Connect code host to create a repository
        </Button>
        <form
          v-else-if="!idea.repository"
          :action="idea.routes.repositoryCreate"
          method="POST">
          <CsrfField />
          <Button
            type="submit"
            size="sm">
            <GitBranch
              class="size-4"
              aria-hidden="true" />
            Create repository
          </Button>
        </form>
        <Button
          v-else-if="!idea.user.hasGithubToken && !showRepositoryInvitePrompt"
          as="a"
          :href="idea.routes.repositoryInvite"
          size="sm">
          <GitBranch
            class="size-4"
            aria-hidden="true" />
          Connect code host to invite collaborators
        </Button>
        <form
          v-else-if="!showRepositoryInvitePrompt"
          :action="idea.routes.repositoryInvite"
          method="POST">
          <CsrfField />
          <Button
            type="submit"
            size="sm">
            <GitBranch
              class="size-4"
              aria-hidden="true" />
            Invite collaborators
          </Button>
        </form>
        <Button
          :as="Link"
          :href="idea.routes.edit"
          variant="outline"
          size="sm">
          <Pencil
            class="size-4"
            aria-hidden="true" />
          Edit
        </Button>
      </div>
    </header>

    <div
      v-if="showRepositoryInvitePrompt"
      class="flex flex-col gap-3 rounded-md border border-primary/25 bg-primary/10 px-4 py-3 text-sm text-muted-foreground sm:flex-row sm:items-center sm:justify-between"
      role="status"
      aria-live="polite">
      <p>
        {{ repositoryInvitePromptText }}
      </p>
      <form
        v-if="canInviteCollaborators"
        :action="idea.routes.repositoryInvite"
        method="POST">
        <CsrfField />
        <Button
          type="submit"
          size="sm">
          <GitBranch
            class="size-4"
            aria-hidden="true" />
          Invite collaborators
        </Button>
      </form>
      <Button
        v-else
        as="a"
        :href="idea.routes.repositoryInvite"
        size="sm">
        <GitBranch
          class="size-4"
          aria-hidden="true" />
        Connect code host
      </Button>
    </div>

    <div
      v-if="showRepositoryAccessPrompt"
      class="flex flex-col gap-1 rounded-md border border-primary/25 bg-primary/10 px-4 py-3 text-sm text-muted-foreground"
      role="status"
      aria-live="polite">
      <p class="font-medium text-foreground">
        Review repository access.
      </p>
      <p>
        A collaborator left or was removed from this idea. Check repository access when you are ready.
      </p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
      <div class="flex flex-col gap-4">
        <div
          class="grid w-full grid-cols-2 gap-1 rounded-md border border-border bg-card p-1 sm:w-fit"
          role="tablist"
          aria-label="Idea management sections">
          <Button
            v-for="tab in manageTabs"
            :id="tab.tabId"
            :key="tab.value"
            :variant="tabVariant(tab.value)"
            :aria-controls="tab.panelId"
            :aria-selected="isSelected(tab.value)"
            :tabindex="tabIndex(tab.value)"
            role="tab"
            class="w-full"
            @click="selectTab(tab.value)"
            @keydown="handleTabKeydown"
          >
            {{ tab.label }}
          </Button>
        </div>

        <Transition
          name="content-fade"
          mode="out-in">
          <div
            v-if="activeTab === 'applications'"
            id="manage-applications-panel"
            key="applications"
            class="flex flex-col gap-3"
            role="tabpanel"
            tabindex="0"
            aria-labelledby="manage-applications-tab"
          >
            <template v-if="applications.items.length > 0">
              <Card
                v-for="application in applications.items"
                :key="application.id">
                <CardHeader>
                  <h6 class="font-medium">
                    <a
                      class="text-primary hover:underline"
                      :href="application.user.routes.show">{{ application.user.name }}'s Application</a>
                  </h6>
                </CardHeader>
                <CardContent class="flex flex-col gap-4">
                  <dl class="grid gap-3 rounded-md border border-border bg-background/35 p-3 text-sm md:grid-cols-2">
                    <div class="flex flex-col gap-1">
                      <dt class="text-xs font-medium uppercase text-muted-foreground">
                        Contribution type
                      </dt>
                      <dd>
                        {{ application.contributionTypeDisplay }}
                      </dd>
                    </div>
                    <div class="flex flex-col gap-1">
                      <dt class="text-xs font-medium uppercase text-muted-foreground">
                        First action
                      </dt>
                      <dd>
                        {{ application.firstAction || 'Not specified' }}
                      </dd>
                    </div>
                  </dl>
                  <MarkdownContent :html="application.contentHtml" />
                  <IdeaApplicationThread
                    v-if="application.thread"
                    :application="application"
                    title="Application thread" />
                  <h6 class="text-right text-sm text-muted-foreground">
                    Submitted {{ application.createdAtForHumans }}
                  </h6>
                  <div class="flex flex-wrap justify-between gap-3 border-t border-border pt-4">
                    <details
                      v-if="idea.can.deleteApplication"
                      class="w-full rounded-md border border-destructive/25 bg-destructive/10 p-3 md:max-w-sm">
                      <summary class="cursor-pointer text-sm font-medium text-destructive">
                        Decline application
                      </summary>
                      <form
                        :action="application.routes.destroy"
                        method="POST"
                        class="mt-3 flex flex-col gap-2">
                        <CsrfField />
                        <MethodField method="DELETE" />
                        <label
                          :for="`decline-reason-${application.id}`"
                          class="text-xs font-medium uppercase text-muted-foreground">
                          Private reason
                        </label>
                        <textarea
                          :id="`decline-reason-${application.id}`"
                          name="decline_reason"
                          class="min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40"
                          maxlength="1200"
                          placeholder="Optional note for the applicant."
                        />
                        <Button
                          type="submit"
                          variant="destructive"
                          size="sm">
                          Confirm decline
                        </Button>
                      </form>
                    </details>
                    <form
                      v-if="idea.can.updateApplication"
                      :action="application.routes.approve"
                      method="POST"
                      class="ml-auto flex w-full flex-col gap-2 md:max-w-sm">
                      <CsrfField />
                      <MethodField method="PUT" />
                      <div
                        v-if="!idea.collaboration.gettingStartedNotesReady"
                        class="rounded-md border border-primary/25 bg-primary/10 px-3 py-2 text-sm text-muted-foreground">
                        Private start notes are missing. You can approve now and add an optional collaborator note below.
                      </div>
                      <label
                        :for="`approval-note-${application.id}`"
                        class="text-xs font-medium uppercase text-muted-foreground">
                        Approval note
                      </label>
                      <textarea
                        :id="`approval-note-${application.id}`"
                        name="approval_note"
                        class="min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40"
                        maxlength="1200"
                        placeholder="Optional private note for this collaborator."
                      />
                      <Button
                        type="submit"
                        variant="success"
                        size="sm">
                        Approve application
                      </Button>
                    </form>
                  </div>
                </CardContent>
              </Card>
            </template>
            <p v-else>
              No pending applications.
            </p>
            <PaginationLinks
              :paginator="applications"
              :only="['applications']"
              label="Application pages" />
          </div>

          <div
            v-else
            id="manage-collaborators-panel"
            key="collaborators"
            class="flex flex-col gap-3"
            role="tabpanel"
            tabindex="0"
            aria-labelledby="manage-collaborators-tab"
          >
            <template v-if="collaborators.items.length > 0">
              <div
                v-for="collaborator in collaborators.items"
                :key="collaborator.id"
                class="flex flex-col items-stretch gap-4 rounded-md border border-border bg-card px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <a
                  class="text-primary hover:underline"
                  :href="collaborator.user.routes.show">
                  {{ collaborator.user.firstName }} {{ collaborator.user.lastName }}
                </a>
                <details
                  v-if="idea.can.deleteApplication"
                  class="w-full rounded-md border border-destructive/25 bg-destructive/10 p-3 sm:max-w-sm">
                  <summary class="cursor-pointer text-sm font-medium text-destructive">
                    Remove collaborator
                  </summary>
                  <form
                    :action="collaborator.routes.destroy"
                    method="POST"
                    class="mt-3 flex flex-col gap-2">
                    <CsrfField />
                    <MethodField method="DELETE" />
                    <label
                      :for="`remove-reason-${collaborator.id}`"
                      class="text-xs font-medium uppercase text-muted-foreground">
                      Private reason
                    </label>
                    <textarea
                      :id="`remove-reason-${collaborator.id}`"
                      name="exit_reason"
                      class="min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40"
                      maxlength="1200"
                      placeholder="Optional note for the collaborator."
                    />
                    <Button
                      type="submit"
                      variant="destructive"
                      size="sm">
                      Confirm remove
                    </Button>
                  </form>
                </details>
              </div>
            </template>
            <p v-else>
              No collaborators have joined yet.
            </p>
            <PaginationLinks
              :paginator="collaborators"
              :only="['collaborators']"
              label="Collaborator pages" />
          </div>
        </Transition>
      </div>

      <aside class="flex flex-col gap-3">
        <Card>
          <CardHeader>
            <div class="flex flex-col gap-1">
              <h2 class="text-lg font-semibold text-white">
                Collaboration readiness
              </h2>
              <p class="text-sm text-muted-foreground">
                Keep the collaboration path clear before people apply.
              </p>
            </div>
          </CardHeader>
          <CardContent>
            <ul class="flex flex-col gap-3">
              <li
                v-for="item in readinessItems"
                :key="item.label"
                class="flex items-start justify-between gap-3 text-sm">
                <span class="flex min-w-0 gap-2">
                  <Check
                    v-if="item.complete"
                    class="mt-0.5 size-4 shrink-0 text-primary"
                    aria-hidden="true" />
                  <Circle
                    v-else
                    class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                    aria-hidden="true" />
                  <span :class="item.complete ? 'text-foreground' : 'text-muted-foreground'">{{ item.label }}</span>
                </span>
                <Button
                  v-if="!item.complete && item.actionHref"
                  as="a"
                  :href="item.actionHref"
                  variant="outline"
                  size="sm">
                  {{ item.actionLabel }}
                </Button>
              </li>
            </ul>
          </CardContent>
        </Card>
      </aside>
    </div>
  </section>
</template>
