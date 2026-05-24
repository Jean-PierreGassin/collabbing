<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import { Link } from '@inertiajs/vue3';
import { ChevronDown, GitBranch, MessageSquare, Sparkles, Users } from '@lucide/vue';
import { useCollapsiblePanelTransition } from '@/composables/useCollapsiblePanelTransition';
import type { Idea } from '@/types/domain';

const props = withDefaults(defineProps<{
  idea: Idea;
  featured?: boolean;
  hideTitle?: boolean;
  pitchExpanded?: boolean;
  single?: boolean;
  showComments?: boolean;
  variant?: 'compact' | 'detailed';
}>(), {
  featured: false,
  hideTitle: false,
  pitchExpanded: false,
  single: false,
  showComments: false,
  variant: 'compact',
});

const emit = defineEmits<{
  'update:pitchExpanded': [value: boolean];
}>();

const {
  beforeEnter: beforeDescriptionEnter,
  enter: enterDescription,
  leave: leaveDescription,
} = useCollapsiblePanelTransition();

const isCompact = computed(() => props.variant === 'compact' && ! props.single);
const isDetailed = computed(() => props.variant === 'detailed' && ! props.single);
const descriptionId = computed(() => `idea-${props.idea.id}-description`);
const isDescriptionExpanded = computed({
  get: () => props.pitchExpanded,
  set: (value: boolean) => emit('update:pitchExpanded', value),
});

const applicationStatusLabel = computed(() => {
  if (props.idea.collaboration.applicationsOpen) {
    return 'Applications open';
  }

  return 'Applications closed';
});

const applicationStatusVariant = computed(() => {
  if (props.idea.collaboration.applicationsOpen) {
    return 'secondary';
  }

  return 'outline';
});

const helpWantedSummary = computed(() => {
  const labels = props.idea.collaboration.helpWantedDisplay;

  if (labels.length === 0) {
    return 'Open to figuring it out';
  }

  const visible = labels.slice(0, 2);
  const hiddenCount = labels.length - visible.length;

  if (hiddenCount === 0) {
    return visible.join(', ');
  }

  return `${visible.join(', ')} +${hiddenCount}`;
});

const collaboratorsLabel = computed(() => {
  const count = props.idea.approvedApplicationsCount;
  let noun = 'collaborators';

  if (count === 1) {
    noun = 'collaborator';
  }

  return `${count.toLocaleString()} ${noun}`;
});

const supportersLabel = computed(() => {
  const count = props.idea.supportersCount;
  let noun = 'supporters';

  if (count === 1) {
    noun = 'supporter';
  }

  return `${count.toLocaleString()} ${noun}`;
});

const pendingApplicationsLabel = computed(() => {
  const count = props.idea.pendingApplicationsCount ?? 0;
  let noun = 'pending applications';

  if (count === 1) {
    noun = 'pending application';
  }

  return `${count.toLocaleString()} ${noun}`;
});

const cardClasses = computed(() => {
  const classes = [];

  if (props.featured) {
    classes.push('h-full border-primary/20 bg-card/95');
  } else {
    classes.push('bg-card/90');
  }

  if (isDetailed.value) {
    classes.push('group relative transition-[border-color,background-color,box-shadow,transform] duration-150 ease-out hover:-translate-y-px hover:border-primary/35 hover:bg-card focus-within:border-primary/55 focus-within:ring-2 focus-within:ring-ring/35');
  } else if (! props.single) {
    classes.push('group relative flex flex-col transition-[border-color,background-color,box-shadow,transform] duration-150 ease-out hover:-translate-y-px hover:border-primary/35 hover:bg-card focus-within:border-primary/55 focus-within:ring-2 focus-within:ring-ring/35');
  }

  return classes;
});

const headerClass = computed(() => {
  if (props.single) {
    return undefined;
  }

  if (isCompact.value) {
    return 'relative z-10 gap-2 pointer-events-none p-4 pb-2';
  }

  return 'relative z-10 gap-4 pointer-events-none';
});

const summaryContentClass = computed(() => {
  if (isCompact.value) {
    return 'relative z-10 flex flex-col gap-2 p-4 pt-0 pointer-events-none';
  }

  return 'relative z-10 flex flex-1 flex-col gap-4 pointer-events-none';
});

const cardStatsClass = computed(() => {
  if (isCompact.value) {
    return 'pointer-events-none relative z-10 flex flex-wrap items-center justify-start gap-2 border-t border-border bg-background/12 px-4 py-2 text-xs text-muted-foreground';
  }

  return 'pointer-events-none relative z-10 flex flex-wrap items-center justify-start gap-2 border-t border-border bg-background/12 px-6 py-3 text-sm text-muted-foreground';
});

const cardActionsClass = computed(() => {
  if (isCompact.value) {
    return 'relative z-20 flex flex-col gap-2 border-t border-border bg-background/18 px-4 py-2';
  }

  return 'relative z-20 flex flex-col gap-3 border-t border-border bg-background/18 px-6 py-4';
});

const pitchToggleLabel = computed(() => {
  if (isDescriptionExpanded.value) {
    return 'Hide Pitch';
  }

  return 'Show Pitch';
});

const pitchChevronClass = computed(() => {
  if (isDescriptionExpanded.value) {
    return 'rotate-180';
  }

  return undefined;
});
</script>

<template>
  <Card :class="cardClasses">
    <Link
      v-if="!single"
      :href="idea.routes.show"
      class="absolute inset-0 z-0 rounded-lg focus-visible:outline-none"
      :aria-label="`Open ${idea.titleDisplay}`"
    />

    <div
      v-if="isCompact"
      class="pointer-events-none relative z-10 flex h-full flex-col gap-2.5 p-3.5">
      <div class="pointer-events-none flex min-w-0 flex-col gap-1.5">
        <div
          v-if="featured || idea.can.update"
          class="flex min-w-0 items-start justify-between gap-3">
          <span
            v-if="featured"
            class="inline-flex min-h-9 items-center gap-1.5 text-xs font-semibold uppercase text-primary">
            <Sparkles
              class="size-3.5"
              aria-hidden="true" />
            Trending
          </span>
          <Button
            v-if="idea.can.update"
            :as="Link"
            :href="idea.routes.dashboard"
            size="sm"
            class="pointer-events-auto relative z-20 ml-auto">
            <GitBranch
              class="size-4"
              aria-hidden="true" />
            Manage
          </Button>
        </div>

        <CardTitle class="leading-tight">
          <span class="text-white transition-colors group-hover:text-primary">{{ idea.titleDisplay }}</span>
        </CardTitle>

        <p class="text-xs text-muted-foreground">
          by
          <Link
            class="pointer-events-auto relative z-20 font-medium text-primary hover:underline"
            :href="idea.user.routes.show">
            @{{ idea.user.username }}
          </Link>
          <span aria-hidden="true"> · </span>
          {{ idea.createdAtForHumans }}
        </p>
      </div>

      <p class="pointer-events-none break-words text-sm leading-5 text-muted-foreground [overflow-wrap:anywhere]">
        {{ idea.tagline }}
      </p>

      <div
        v-if="idea.tags.length > 0"
        class="pointer-events-none flex flex-wrap gap-1.5 pt-0.5">
        <Badge
          v-for="tag in idea.tags"
          :key="tag"
          variant="secondary"
          class="text-xs">
          {{ tag }}
        </Badge>
      </div>

      <div class="pointer-events-none mt-auto flex flex-wrap items-center gap-x-3 gap-y-2 border-t border-border pt-2.5 text-xs text-muted-foreground">
        <span class="inline-flex items-center gap-1.5">
          <Users
            class="size-3.5 text-primary"
            aria-hidden="true" />
          {{ supportersLabel }}
        </span>
        <span class="inline-flex items-center gap-1.5">
          <MessageSquare
            class="size-3.5 text-primary"
            aria-hidden="true" />
          {{ collaboratorsLabel }}
        </span>
        <span
          v-if="idea.can.update && idea.pendingApplicationsCount"
          class="inline-flex items-center gap-1.5">
          <MessageSquare
            class="size-3.5 text-primary"
            aria-hidden="true" />
          {{ pendingApplicationsLabel }}
        </span>
      </div>
    </div>

    <div
      v-else-if="isDetailed"
      class="pointer-events-none relative z-10 flex flex-col gap-2.5 p-4 sm:px-5">
      <div class="flex min-w-0 flex-col gap-2.5">
        <div class="flex min-w-0 items-start justify-between gap-4">
          <div class="flex min-w-0 flex-col gap-2">
            <div
              v-if="featured"
              class="flex flex-wrap items-center gap-1.5">
              <span class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase text-primary">
                <Sparkles
                  class="size-3.5"
                  aria-hidden="true" />
                Trending
              </span>
            </div>

            <div class="flex min-w-0 flex-col gap-1">
              <CardTitle class="text-lg leading-tight sm:text-xl">
                <span class="text-white transition-colors group-hover:text-primary">{{ idea.titleDisplay }}</span>
              </CardTitle>
              <p class="text-sm text-muted-foreground">
                by
                <Link
                  class="pointer-events-auto relative z-20 font-medium text-primary hover:underline"
                  :href="idea.user.routes.show">
                  @{{ idea.user.username }}
                </Link>
                <span aria-hidden="true"> · </span>
                {{ idea.createdAtForHumans }}
              </p>
            </div>
          </div>

          <Button
            v-if="idea.can.update"
            :as="Link"
            :href="idea.routes.dashboard"
            size="sm"
            class="pointer-events-auto relative z-20 shrink-0">
            <GitBranch
              class="size-4"
              aria-hidden="true" />
            Manage
          </Button>
        </div>

        <p class="break-words text-base leading-6 text-foreground [overflow-wrap:anywhere]">
          {{ idea.tagline }}
        </p>
      </div>

      <div class="flex min-w-0 flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex min-w-0 flex-col gap-2">
          <div class="flex min-w-0 flex-wrap gap-1.5">
            <Badge
              v-for="tag in idea.tags"
              :key="tag"
              variant="secondary"
              class="text-xs">
              {{ tag }}
            </Badge>
            <span
              v-if="idea.tags.length === 0"
              class="text-sm text-muted-foreground">No tags yet.</span>
          </div>
        </div>

        <div class="flex shrink-0 flex-wrap items-center gap-3 text-xs text-muted-foreground sm:justify-end">
          <span class="inline-flex items-center gap-1.5">
            <Users
              class="size-3.5 text-primary"
              aria-hidden="true" />
            <strong class="font-semibold text-white">{{ idea.supportersCount.toLocaleString() }}</strong>
            supporters
          </span>
          <span class="inline-flex items-center gap-1.5">
            <MessageSquare
              class="size-3.5 text-primary"
              aria-hidden="true" />
            <strong class="font-semibold text-white">{{ idea.approvedApplicationsCount.toLocaleString() }}</strong>
            collaborators
          </span>
          <span
            v-if="idea.can.update && idea.pendingApplicationsCount"
            class="inline-flex items-center gap-1.5">
            <MessageSquare
              class="size-3.5 text-primary"
              aria-hidden="true" />
            <strong class="font-semibold text-white">{{ idea.pendingApplicationsCount.toLocaleString() }}</strong>
            pending
          </span>
        </div>
      </div>
    </div>

    <CardHeader
      v-else
      :class="headerClass">
      <div class="flex min-w-0 items-start justify-between gap-3">
        <div class="flex min-w-0 flex-col gap-2">
          <div
            v-if="featured"
            class="flex items-center gap-2 text-xs font-medium uppercase text-primary">
            <Sparkles
              class="size-3.5"
              aria-hidden="true" />
            Trending
          </div>
          <template v-if="single && !hideTitle">
            <h1 class="text-2xl font-semibold leading-tight text-white">
              {{ idea.titleDisplay }}
            </h1>
          </template>
          <CardTitle
            v-else-if="!single"
            class="leading-tight">
            <span class="text-white transition-colors group-hover:text-primary">{{ idea.titleDisplay }}</span>
          </CardTitle>
          <p class="text-xs text-muted-foreground">
            by
            <Link
              class="pointer-events-auto relative z-20 font-medium text-primary hover:underline"
              :href="idea.user.routes.show">
              @{{ idea.user.username }}
            </Link>
            <span aria-hidden="true"> · </span>
            {{ idea.createdAtForHumans }}
          </p>
        </div>
        <Badge
          :variant="applicationStatusVariant"
          class="shrink-0"
          :aria-label="`Application status: ${applicationStatusLabel}`">
          {{ applicationStatusLabel }}
        </Badge>
      </div>
    </CardHeader>

    <CardContent
      v-if="single"
      class="flex flex-col gap-5">
      <section
        class="border-b border-border pb-4"
        aria-label="Project context">
        <h2 class="mb-3 text-sm font-semibold text-white">
          Project context
        </h2>
        <dl class="grid gap-3 text-sm sm:grid-cols-2">
          <div class="min-w-0">
            <dt class="text-xs font-medium uppercase text-muted-foreground">
              Stage
            </dt>
            <dd class="truncate text-foreground">
              {{ idea.collaboration.stageDisplay }}
            </dd>
          </div>
          <div class="min-w-0">
            <dt class="text-xs font-medium uppercase text-muted-foreground">
              Help
            </dt>
            <dd class="truncate text-foreground">
              {{ helpWantedSummary }}
            </dd>
          </div>
        </dl>
      </section>

      <section
        class="flex flex-col gap-2"
        aria-label="Idea summary">
        <h2 class="text-sm font-semibold text-white">
          Summary
        </h2>
        <p class="break-words text-base leading-7 text-white [overflow-wrap:anywhere]">
          {{ idea.summary }}
        </p>
        <div
          v-if="idea.tags.length > 0"
          class="flex flex-wrap gap-2 pt-1">
          <Badge
            v-for="tag in idea.tags"
            :key="tag"
            variant="secondary"
            class="text-xs">
            {{ tag }}
          </Badge>
        </div>
      </section>

      <section class="flex flex-col gap-3">
        <button
          type="button"
          class="group flex w-full items-center gap-3 text-primary transition-colors hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
          :aria-controls="descriptionId"
          :aria-expanded="isDescriptionExpanded"
          @click="isDescriptionExpanded = !isDescriptionExpanded"
        >
          <span
            class="h-px flex-1 bg-primary/35 transition-colors group-hover:bg-primary/65"
            aria-hidden="true" />
          <span class="inline-flex items-center gap-1 text-sm font-semibold">
            {{ pitchToggleLabel }}
            <ChevronDown
              :class="[
                'size-4 transition-transform duration-200',
                pitchChevronClass,
              ]"
              aria-hidden="true" />
          </span>
          <span
            class="h-px flex-1 bg-primary/35 transition-colors group-hover:bg-primary/65"
            aria-hidden="true" />
        </button>

        <Transition
          @before-enter="beforeDescriptionEnter"
          @enter="enterDescription"
          @leave="leaveDescription"
        >
          <div
            v-if="isDescriptionExpanded"
            :id="descriptionId"
            class="description-reveal-panel">
            <slot name="pitch-toc" />
            <div class="min-h-0 min-w-0 overflow-hidden border-t border-border pt-4">
              <MarkdownContent :html="idea.contentHtml" />
            </div>
          </div>
        </Transition>
      </section>
    </CardContent>

    <CardContent
      v-else-if="!isCompact && !isDetailed"
      :class="summaryContentClass">
      <p class="break-words text-sm leading-6 text-muted-foreground [overflow-wrap:anywhere]">
        {{ idea.tagline }}
      </p>
      <div
        v-if="idea.tags.length > 0"
        class="flex flex-wrap gap-1.5">
        <Badge
          v-for="tag in idea.tags"
          :key="tag"
          variant="secondary"
          class="text-xs">
          {{ tag }}
        </Badge>
      </div>
    </CardContent>

    <div
      v-if="!single && !isCompact && !isDetailed"
      :class="cardStatsClass">
      <span class="inline-flex items-center gap-2">
        <Users
          class="size-4 text-primary"
          aria-hidden="true" />
        {{ supportersLabel }}
      </span>
      <span
        class="text-border"
        aria-hidden="true">/</span>
      <span class="inline-flex items-center gap-2">
        <MessageSquare
          class="size-4 text-primary"
          aria-hidden="true" />
        {{ collaboratorsLabel }}
      </span>
      <template v-if="idea.can.update && idea.pendingApplicationsCount">
        <span
          class="text-border"
          aria-hidden="true">/</span>
        <span class="inline-flex items-center gap-2">
          <MessageSquare
            class="size-4 text-primary"
            aria-hidden="true" />
          {{ pendingApplicationsLabel }}
        </span>
      </template>
    </div>

    <div
      v-if="!single && !isCompact && !isDetailed && (idea.can.update || idea.repository)"
      :class="cardActionsClass">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div
          v-if="idea.repository"
          class="inline-flex items-center gap-2 text-sm text-muted-foreground">
          <GitBranch
            class="size-4 text-primary"
            aria-hidden="true" />
          Repository linked
        </div>

        <Button
          v-if="idea.can.update"
          :as="Link"
          :href="idea.routes.dashboard"
          size="sm"
          class="ml-auto">
          <GitBranch
            class="size-4"
            aria-hidden="true" />
          Manage
        </Button>
      </div>
    </div>
  </Card>
</template>
