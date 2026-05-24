<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import UserAvatar from '@/components/users/UserAvatar.vue';
import type { Idea, IdeaApplication, IdeaSupporter } from '@/types/domain';

const props = defineProps<{
  idea: Idea;
  collaborator?: IdeaApplication | null;
  applicant?: IdeaApplication | null;
  supporter?: IdeaSupporter | null;
}>();

const supportForm = useForm({});
const localSupporter = ref<IdeaSupporter | null>(props.supporter ?? null);
const localSupportersCount = ref(props.idea.supportersCount);
const supportSparkKey = ref(0);
const showSupportSparks = ref(false);

const supporterSentence = computed(() => {
  if (localSupportersCount.value === 0) {
    return 'No one is supporting this idea yet.';
  }

  let verb = 'is';
  let noun = 'person';

  if (localSupportersCount.value > 1) {
    verb = 'are';
    noun = 'people';
  }

  return `There ${verb} ${localSupportersCount.value.toLocaleString()} ${noun} supporting this idea.`;
});

watch(
  () => props.supporter,
  (supporter) => {
    localSupporter.value = supporter ?? null;
  },
);

watch(
  () => props.idea.supportersCount,
  (count) => {
    localSupportersCount.value = count;
  },
);

function playSupportSparks(): void {
  supportSparkKey.value += 1;
  showSupportSparks.value = true;

  window.setTimeout(() => {
    showSupportSparks.value = false;
  }, 760);
}

function supportIdea(): void {
  supportForm.post(props.idea.routes.supportersStore, {
    preserveScroll: true,
    onSuccess: () => {
      localSupportersCount.value = props.idea.supportersCount;
      playSupportSparks();
    },
  });
}

function removeSupport(): void {
  const supporter = localSupporter.value;

  if (! supporter) {
    return;
  }

  supportForm.delete(supporter.routes.destroy, {
    preserveScroll: true,
    onSuccess: () => {
      localSupporter.value = null;
      localSupportersCount.value = props.idea.supportersCount;
    },
  });
}
</script>

<template>
  <div class="flex flex-col gap-3">
    <Card>
      <CardHeader>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <h2 class="text-lg font-semibold text-white">
            Collaborators
          </h2>
          <div v-if="collaborator || applicant || idea.can.storeApplication">
            <Button
              v-if="collaborator"
              type="button"
              size="sm"
              disabled>
              Collaborator
            </Button>
            <Button
              v-else-if="applicant"
              type="button"
              size="sm"
              disabled>
              Application pending
            </Button>
            <Button
              v-else
              as="a"
              :href="idea.routes.applicationsCreate"
              variant="outline"
              size="sm">
              Apply to Collaborate
            </Button>
          </div>
        </div>
      </CardHeader>
      <CardContent>
        <template v-if="idea.collaborators.length === 0">
          No collaborators have joined yet.
        </template>
        <div
          v-else
          class="flex flex-wrap gap-2">
          <a
            v-for="collab in idea.collaborators"
            :key="collab.id"
            :href="collab.user.routes.show">
            <UserAvatar
              :src="collab.user.profilePicture"
              :alt="`${collab.user.firstName} ${collab.user.lastName}`"
              size="sm"
              class="border-secondary"
            />
          </a>
          <span
            v-if="idea.hiddenCollaboratorsCount > 0"
            class="inline-flex h-8 items-center rounded-md border border-border bg-background/35 px-2 text-xs font-medium text-muted-foreground">
            +{{ idea.hiddenCollaboratorsCount.toLocaleString() }} more
          </span>
        </div>
      </CardContent>
    </Card>

    <Card v-if="idea.repository || idea.repositoryActivity.isMissing || idea.repositoryActivity.events.length > 0">
      <CardHeader>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <h2 class="text-lg font-semibold text-white">
            Repository
          </h2>
          <Button
            v-if="idea.repositoryActivity.htmlUrl"
            as="a"
            :href="idea.repositoryActivity.htmlUrl"
            target="_blank"
            rel="noopener noreferrer"
            variant="outline"
            size="sm"
          >
            View on GitHub
          </Button>
          <Button
            v-if="idea.repository || idea.repositoryActivity.isMissing || idea.repositoryActivity.events.length > 0"
            as="a"
            :href="idea.routes.repositoryActivity"
            variant="ghost"
            size="sm">
            Activity history
          </Button>
        </div>
      </CardHeader>
      <CardContent class="flex flex-col gap-4 text-sm">
        <div
          v-if="idea.repositoryActivity.isMissing"
          class="rounded-md border border-destructive/40 bg-destructive/10 px-3 py-2 text-destructive">
          Repository is no longer available on GitHub.
        </div>

        <template v-else>
          <div
            v-if="idea.repositoryActivity.latestCommitMessage"
            class="flex flex-col gap-1">
            <span class="text-xs uppercase text-muted-foreground">Latest commit</span>
            <span>{{ idea.repositoryActivity.latestCommitMessage }}</span>
            <span class="text-muted-foreground">
              <template v-if="idea.repositoryActivity.latestCommitAuthor">
                {{ idea.repositoryActivity.latestCommitAuthor }}
              </template>
              <template v-if="idea.repositoryActivity.latestCommitShortSha">
                - {{ idea.repositoryActivity.latestCommitShortSha }}
              </template>
            </span>
          </div>

          <div class="grid grid-cols-3 gap-2 text-center">
            <div class="rounded-md border border-border px-2 py-2">
              <div class="font-semibold">
                {{ idea.repositoryActivity.openIssuesCount.toLocaleString() }}
              </div>
              <div class="text-xs text-muted-foreground">
                Issues
              </div>
            </div>
            <div class="rounded-md border border-border px-2 py-2">
              <div class="font-semibold">
                {{ idea.repositoryActivity.stargazersCount.toLocaleString() }}
              </div>
              <div class="text-xs text-muted-foreground">
                Stars
              </div>
            </div>
            <div class="rounded-md border border-border px-2 py-2">
              <div class="font-semibold">
                {{ idea.repositoryActivity.forksCount.toLocaleString() }}
              </div>
              <div class="text-xs text-muted-foreground">
                Forks
              </div>
            </div>
          </div>

          <div class="text-muted-foreground">
            <template v-if="idea.repositoryActivity.lastPushedAtForHumans">
              Last pushed {{ idea.repositoryActivity.lastPushedAtForHumans }}.
            </template>
            <template v-if="idea.repositoryActivity.lastSyncedAtForHumans">
              Synced {{ idea.repositoryActivity.lastSyncedAtForHumans }}.
            </template>
          </div>
        </template>

        <div
          v-if="idea.repositoryActivity.events.length > 0"
          class="flex flex-col gap-2 border-t border-border pt-3">
          <div
            v-for="event in idea.repositoryActivity.events"
            :key="event.id"
            class="flex flex-col gap-1">
            <span>{{ event.summary }}</span>
            <span class="text-xs text-muted-foreground">{{ event.occurredAtForHumans }}</span>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardHeader>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <h2 class="text-lg font-semibold text-white">
            Supporters
          </h2>
          <div
            v-if="idea.can.storeSupporter"
            class="relative">
            <Button
              v-if="localSupporter"
              type="button"
              size="sm"
              variant="outline"
              :disabled="supportForm.processing"
              @click="removeSupport">
              Remove Support
            </Button>
            <Button
              v-else
              type="button"
              size="sm"
              variant="outline"
              :disabled="supportForm.processing"
              @click="supportIdea">
              Support Idea
            </Button>
            <span
              v-if="showSupportSparks"
              :key="supportSparkKey"
              class="auth-success-sparks"
              aria-hidden="true">
              <span
                v-for="index in 12"
                :key="index" />
            </span>
          </div>
        </div>
      </CardHeader>
      <CardContent>
        {{ supporterSentence }}
      </CardContent>
    </Card>
  </div>
</template>
