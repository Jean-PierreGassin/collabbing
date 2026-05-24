<script setup lang="ts">
import { computed, ref } from 'vue';
import CommentList from '@/components/comments/CommentList.vue';
import IdeaApplicationThread from '@/components/ideas/IdeaApplicationThread.vue';
import IdeaCard from '@/components/ideas/IdeaCard.vue';
import IdeaSidebar from '@/components/ideas/IdeaSidebar.vue';
import IdeaStartCollaboratingPanel from '@/components/ideas/IdeaStartCollaboratingPanel.vue';
import { Button } from '@/components/ui/button';
import MarkdownTableOfContents from '@/components/typography/MarkdownTableOfContents.vue';
import { markdownHeadings, stripGeneratedTableOfContents } from '@/lib/markdown';
import { GitBranch, Pencil } from '@lucide/vue';
import type { DomainUser, Idea, IdeaApplication, IdeaComment, IdeaSupporter, Paginator } from '@/types/domain';

const props = defineProps<{
  idea: Idea;
  comments: Paginator<IdeaComment>;
  collaborator: IdeaApplication | null;
  applicant: IdeaApplication | null;
  historicalApplication: IdeaApplication | null;
  supporter: IdeaSupporter | null;
}>();

const isPitchExpanded = ref(false);

const mentionableUsers = computed<DomainUser[]>(() => {
  const users = [
    props.idea.user,
    ...props.idea.collaborators.map((collaborator) => collaborator.user),
  ];
  const seen = new Set<number>();

  return users.filter((user) => {
    if (seen.has(user.id)) {
      return false;
    }

    seen.add(user.id);

    return true;
  });
});

const pitchContent = computed(() => stripGeneratedTableOfContents(props.idea.content));
const pitchHeadings = computed(() => markdownHeadings(pitchContent.value));
const pitchDescriptionId = computed(() => `idea-${props.idea.id}-description`);
const threadApplication = computed(() => props.applicant ?? props.collaborator ?? props.historicalApplication);

function hasPitchHeadingsClass(): string | undefined {
  if (pitchHeadings.value.length > 0) {
    return 'relative flex flex-col gap-4';
  }

  return undefined;
}
</script>

<template>
  <section class="flex flex-col gap-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
      <div class="flex min-w-0 flex-col gap-1">
        <h1 class="text-2xl font-semibold leading-tight text-white">
          {{ idea.titleDisplay }}
        </h1>
      </div>
      <div
        v-if="idea.can.update"
        class="flex shrink-0 flex-wrap justify-end gap-2 sm:pt-0.5">
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
      </div>
    </header>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
      <IdeaStartCollaboratingPanel
        class="lg:hidden"
        :idea="idea"
        :collaborator="collaborator"
        :applicant="applicant"
      />

      <div class="flex flex-col gap-4">
        <div :class="hasPitchHeadingsClass()">
          <IdeaCard
            v-model:pitch-expanded="isPitchExpanded"
            :idea="idea"
            single
            hide-title />

          <MarkdownTableOfContents
            :content-id="pitchDescriptionId"
            :enabled="isPitchExpanded"
            :headings="pitchHeadings"
            nav-label="Pitch table of contents"
          />
        </div>

        <IdeaApplicationThread
          v-if="threadApplication?.thread"
          id="application-thread"
          :application="threadApplication"
          title="Your application thread" />

        <CommentList
          :can-store-comment="idea.can.storeComment"
          :comments="comments"
          :comments-store="idea.routes.commentsStore"
          :mentionable-users="mentionableUsers" />
      </div>

      <IdeaSidebar
        class="hidden lg:flex"
        :idea="idea"
        :collaborator="collaborator"
        :applicant="applicant"
        :supporter="supporter"
      />

      <IdeaSidebar
        class="lg:hidden"
        :idea="idea"
        :collaborator="collaborator"
        :applicant="applicant"
        :show-start-panel="false"
        :supporter="supporter"
      />
    </div>
  </section>
</template>
