<script setup lang="ts">
import { AlertTriangle, ExternalLink, GitCommit, RefreshCw } from '@lucide/vue';
import { computed, ref } from 'vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import UserAvatar from '@/components/users/UserAvatar.vue';
import type { Idea, IdeaApplication, IdeaSupporter, RepositoryEvent } from '@/types/domain';

const props = defineProps<{
  idea: Idea;
  collaborator?: IdeaApplication | null;
  applicant?: IdeaApplication | null;
  supporter?: IdeaSupporter | null;
}>();

const supporterSentence = computed(() => {
  if (props.idea.supportersCount === 0) {
    return 'No one is supporting this idea yet.';
  }

  let verb = 'is';
  let noun = 'person';

  if (props.idea.supportersCount > 1) {
    verb = 'are';
    noun = 'people';
  }

  return `There ${verb} ${props.idea.supportersCount.toLocaleString()} ${noun} supporting this idea.`;
});

const repositoryPreviewEventLimit = 3;
const olderRepositoryEventsExpanded = ref(false);

const repositoryTimelineId = computed(() => `repository-activity-timeline-${props.idea.id}`);
const olderRepositoryEventsId = computed(() => `repository-older-events-${props.idea.id}`);
const recentRepositoryEvents = computed(() => props.idea.repositoryActivity.events.slice(0, repositoryPreviewEventLimit));
const olderRepositoryEvents = computed(() => props.idea.repositoryActivity.events.slice(repositoryPreviewEventLimit));
const hasRepositoryActivity = computed(() => props.idea.repositoryActivity.events.length > 0);

const repositoryState = computed(() => {
  if (props.idea.repositoryActivity.isMissing) {
    return {
      label: 'Missing',
      title: 'Repository unavailable',
      description: 'GitHub no longer reports this repository as available.',
      tone: 'destructive',
      icon: AlertTriangle,
    };
  }

  if (props.idea.repositoryActivity.latestCommitMessage) {
    let description = 'Latest commit captured from GitHub.';

    if (props.idea.repositoryActivity.latestCommitAuthor && props.idea.repositoryActivity.latestCommitShortSha) {
      description = `${props.idea.repositoryActivity.latestCommitAuthor} - ${props.idea.repositoryActivity.latestCommitShortSha}`;
    } else if (props.idea.repositoryActivity.latestCommitAuthor) {
      description = props.idea.repositoryActivity.latestCommitAuthor;
    } else if (props.idea.repositoryActivity.latestCommitShortSha) {
      description = props.idea.repositoryActivity.latestCommitShortSha;
    }

    return {
      label: 'Latest commit',
      title: props.idea.repositoryActivity.latestCommitMessage,
      description,
      tone: 'default',
      icon: GitCommit,
    };
  }

  if (props.idea.repositoryActivity.lastSyncedAtForHumans) {
    return {
      label: 'Synced',
      title: 'Repository sync is current',
      description: `Synced ${props.idea.repositoryActivity.lastSyncedAtForHumans}.`,
      tone: 'default',
      icon: RefreshCw,
    };
  }

  return {
    label: 'Waiting for sync',
    title: 'Repository linked',
    description: 'Activity will appear after the next GitHub sync.',
    tone: 'muted',
    icon: RefreshCw,
  };
});

function repositoryEventLabel(event: RepositoryEvent): string {
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

function repositoryEventMarkerClass(event: RepositoryEvent): string {
  if (event.type === 'repository_missing') {
    return 'border-destructive/50 bg-destructive/20 text-destructive';
  }

  if (event.type === 'repository_commit') {
    return 'border-primary/50 bg-primary/15 text-primary';
  }

  return 'border-border bg-background text-muted-foreground';
}
</script>

<template>
  <div class="flex flex-col gap-3">
    <Card>
      <CardHeader>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <h2 class="text-lg font-semibold text-white">Collaborators</h2>
          <div v-if="collaborator || applicant || idea.can.storeApplication">
            <Button v-if="collaborator" type="button" size="sm" disabled>Collaborator</Button>
            <Button v-else-if="applicant" type="button" size="sm" disabled>Application pending</Button>
            <Button v-else as="a" :href="idea.routes.applicationsCreate" variant="outline" size="sm">Apply to Collaborate</Button>
          </div>
        </div>
      </CardHeader>
      <CardContent>
        <template v-if="idea.collaborators.length === 0">
          No collaborators have joined yet.
        </template>
        <div v-else class="flex flex-wrap gap-2">
          <a v-for="collab in idea.collaborators" :key="collab.id" :href="collab.user.routes.show">
            <UserAvatar
              :src="collab.user.profilePicture"
              :alt="`${collab.user.firstName} ${collab.user.lastName}`"
              size="sm"
              class="border-secondary"
            />
          </a>
          <span v-if="idea.hiddenCollaboratorsCount > 0" class="inline-flex h-8 items-center rounded-md border border-border bg-background/35 px-2 text-xs font-medium text-muted-foreground">
            +{{ idea.hiddenCollaboratorsCount.toLocaleString() }} more
          </span>
        </div>
      </CardContent>
    </Card>

    <Card v-if="idea.repository || idea.repositoryActivity.isMissing || idea.repositoryActivity.events.length > 0">
      <CardHeader>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div class="flex flex-col gap-1">
            <h2 class="text-lg font-semibold text-white">Repository</h2>
            <p v-if="idea.repositoryName" class="break-all text-sm text-muted-foreground">{{ idea.repositoryName }}</p>
          </div>
          <Button
            v-if="idea.repositoryActivity.htmlUrl"
            as="a"
            :href="idea.repositoryActivity.htmlUrl"
            target="_blank"
            rel="noopener noreferrer"
            variant="outline"
            size="sm"
            class="w-full sm:w-auto"
          >
            Open repository on GitHub
            <ExternalLink aria-hidden="true" />
          </Button>
        </div>
      </CardHeader>
      <CardContent class="flex flex-col gap-4 text-sm">
        <div
          :class="[
            'flex gap-3 rounded-md border px-3 py-3',
            repositoryState.tone === 'destructive' ? 'border-destructive/40 bg-destructive/10 text-destructive' : 'border-border bg-background/35',
          ]"
        >
          <component
            :is="repositoryState.icon"
            :class="[
              'mt-0.5 size-4 shrink-0',
              repositoryState.tone === 'destructive' ? 'text-destructive' : 'text-primary',
            ]"
            aria-hidden="true"
          />
          <div class="min-w-0 flex-1">
            <div class="text-xs font-medium uppercase text-muted-foreground">{{ repositoryState.label }}</div>
            <div class="mt-1 break-words font-medium text-foreground">{{ repositoryState.title }}</div>
            <div class="mt-1 break-words text-muted-foreground">{{ repositoryState.description }}</div>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-2 text-center sm:grid-cols-3">
          <div class="rounded-md border border-border px-2 py-2">
            <div class="font-semibold">{{ idea.repositoryActivity.openIssuesCount.toLocaleString() }}</div>
            <div class="text-xs text-muted-foreground">Issues</div>
          </div>
          <div class="rounded-md border border-border px-2 py-2">
            <div class="font-semibold">{{ idea.repositoryActivity.stargazersCount.toLocaleString() }}</div>
            <div class="text-xs text-muted-foreground">Stars</div>
          </div>
          <div class="rounded-md border border-border px-2 py-2">
            <div class="font-semibold">{{ idea.repositoryActivity.forksCount.toLocaleString() }}</div>
            <div class="text-xs text-muted-foreground">Forks</div>
          </div>
        </div>

        <dl v-if="idea.repositoryActivity.lastPushedAtForHumans || idea.repositoryActivity.lastSyncedAtForHumans" class="grid grid-cols-1 gap-2 text-muted-foreground sm:grid-cols-2">
          <div v-if="idea.repositoryActivity.lastPushedAtForHumans" class="rounded-md border border-border px-3 py-2">
            <dt class="text-xs uppercase">Last pushed</dt>
            <dd class="mt-1 text-foreground">{{ idea.repositoryActivity.lastPushedAtForHumans }}</dd>
          </div>
          <div v-if="idea.repositoryActivity.lastSyncedAtForHumans" class="rounded-md border border-border px-3 py-2">
            <dt class="text-xs uppercase">Last synced</dt>
            <dd class="mt-1 text-foreground">{{ idea.repositoryActivity.lastSyncedAtForHumans }}</dd>
          </div>
        </dl>

        <div class="border-t border-border pt-4">
          <div class="flex items-center justify-between gap-3">
            <h3 class="text-sm font-semibold text-foreground">Activity timeline</h3>
            <span v-if="hasRepositoryActivity" class="text-xs text-muted-foreground">{{ idea.repositoryActivity.events.length.toLocaleString() }} events</span>
          </div>

          <p v-if="!hasRepositoryActivity" class="mt-2 text-muted-foreground">No repository events have been recorded yet.</p>

          <ol v-else :id="repositoryTimelineId" class="mt-3 flex flex-col gap-3" aria-label="Repository activity timeline">
            <li v-for="event in recentRepositoryEvents" :key="event.id" class="grid grid-cols-[auto_1fr] gap-3">
              <span :class="['mt-0.5 inline-flex h-7 min-w-14 items-center justify-center rounded-full border px-2 text-[0.7rem] font-medium', repositoryEventMarkerClass(event)]">
                {{ repositoryEventLabel(event) }}
              </span>
              <div class="min-w-0 border-l border-border pl-3">
                <p class="break-words text-foreground">{{ event.summary }}</p>
                <p class="mt-1 text-xs text-muted-foreground">{{ event.occurredAtForHumans }}</p>
              </div>
            </li>
          </ol>

          <div v-if="olderRepositoryEvents.length > 0" class="mt-3 flex flex-col gap-3">
            <Button
              type="button"
              variant="ghost"
              size="sm"
              class="w-full justify-center sm:w-auto"
              :aria-expanded="olderRepositoryEventsExpanded.toString()"
              :aria-controls="olderRepositoryEventsId"
              @click="olderRepositoryEventsExpanded = !olderRepositoryEventsExpanded"
            >
              {{ olderRepositoryEventsExpanded ? 'Hide older events' : `Show ${olderRepositoryEvents.length.toLocaleString()} older events` }}
            </Button>

            <ol
              v-show="olderRepositoryEventsExpanded"
              :id="olderRepositoryEventsId"
              class="flex flex-col gap-3"
              aria-label="Older repository activity"
            >
              <li v-for="event in olderRepositoryEvents" :key="event.id" class="grid grid-cols-[auto_1fr] gap-3">
                <span :class="['mt-0.5 inline-flex h-7 min-w-14 items-center justify-center rounded-full border px-2 text-[0.7rem] font-medium', repositoryEventMarkerClass(event)]">
                  {{ repositoryEventLabel(event) }}
                </span>
                <div class="min-w-0 border-l border-border pl-3">
                  <p class="break-words text-foreground">{{ event.summary }}</p>
                  <p class="mt-1 text-xs text-muted-foreground">{{ event.occurredAtForHumans }}</p>
                </div>
              </li>
            </ol>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardHeader>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <h2 class="text-lg font-semibold text-white">Supporters</h2>
          <div v-if="idea.can.storeSupporter">
            <form v-if="supporter" :action="supporter.routes.destroy" method="POST">
              <CsrfField />
              <MethodField method="DELETE" />
              <Button type="submit" size="sm" variant="outline">Remove Support</Button>
            </form>
            <form v-else :action="idea.routes.supportersStore" method="POST">
              <CsrfField />
              <Button type="submit" size="sm" variant="outline">Support Idea</Button>
            </form>
          </div>
        </div>
      </CardHeader>
      <CardContent>
        {{ supporterSentence }}
      </CardContent>
    </Card>
  </div>
</template>
