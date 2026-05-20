<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import { Link } from '@inertiajs/vue3';
import { ChevronDown, GitBranch, MessageSquare, Sparkles, Users } from '@lucide/vue';
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

const isCompact = computed(() => props.variant === 'compact' && ! props.single);
const isDetailed = computed(() => props.variant === 'detailed' && ! props.single);
const descriptionId = computed(() => `idea-${props.idea.id}-description`);
const isDescriptionExpanded = computed({
  get: () => props.pitchExpanded,
  set: (value: boolean) => emit('update:pitchExpanded', value),
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
    classes.push('group relative flex h-full min-h-[10rem] flex-col transition-[border-color,background-color,box-shadow,transform] duration-150 ease-out hover:-translate-y-px hover:border-primary/35 hover:bg-card focus-within:border-primary/55 focus-within:ring-2 focus-within:ring-ring/35');
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
    return 'relative z-10 flex flex-1 flex-col gap-2 p-4 pt-0 pointer-events-none';
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

const statusBadgeVariant = computed(() => {
  if (props.idea.status === 'open') {
    return 'default';
  }

  return 'secondary';
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

function resetPanelStyles(element: HTMLElement): void {
  element.style.height = '';
  element.style.opacity = '';
  element.style.overflow = '';
  element.style.transform = '';
  element.style.transition = '';
  element.style.willChange = '';
}

function finishPanelTransition(panel: HTMLElement, propertyName: string, done: () => void, fallbackDelay: number): void {
  let isFinished = false;
  const timer = window.setTimeout(finish, fallbackDelay);

  function finish(): void {
    if (isFinished) {
      return;
    }

    isFinished = true;
    window.clearTimeout(timer);
    panel.removeEventListener('transitionend', handleTransitionEnd);
    resetPanelStyles(panel);
    done();
  }

  function handleTransitionEnd(event: TransitionEvent): void {
    if (event.target === panel && event.propertyName === propertyName) {
      finish();
    }
  }

  panel.addEventListener('transitionend', handleTransitionEnd);
}

function beforeDescriptionEnter(element: Element): void {
  const panel = element as HTMLElement;

  panel.style.height = '0';
  panel.style.opacity = '0';
  panel.style.overflow = 'hidden';
  panel.style.transform = 'translateY(-0.35rem)';
  panel.style.willChange = 'height, opacity, transform';
}

function enterDescription(element: Element, done: () => void): void {
  const panel = element as HTMLElement;

  panel.style.transition = 'height 240ms ease, opacity 220ms ease, transform 220ms ease';

  requestAnimationFrame(() => {
    panel.style.height = `${panel.scrollHeight}px`;
    panel.style.opacity = '1';
    panel.style.transform = 'translateY(0)';
  });

  finishPanelTransition(panel, 'height', done, 320);
}

function leaveDescription(element: Element, done: () => void): void {
  const panel = element as HTMLElement;

  panel.style.height = `${panel.scrollHeight}px`;
  panel.style.opacity = '1';
  panel.style.overflow = 'hidden';
  panel.style.transform = 'translateY(0)';
  panel.style.transition = 'height 220ms ease, opacity 180ms ease, transform 180ms ease';

  requestAnimationFrame(() => {
    panel.style.height = '0';
    panel.style.opacity = '0';
    panel.style.transform = 'translateY(-0.25rem)';
  });

  finishPanelTransition(panel, 'height', done, 300);
}
</script>

<template>
  <Card :class="cardClasses">
    <Link
      v-if="!single"
      :href="idea.routes.show"
      class="absolute inset-0 z-0 rounded-lg focus-visible:outline-none"
      :aria-label="`Open ${idea.titleDisplay}`"
    />

    <div v-if="isDetailed" class="relative z-10 grid gap-0 lg:grid-cols-[minmax(0,1fr)_15rem]">
      <div class="pointer-events-none flex min-w-0 flex-col gap-4 p-5 sm:p-6">
        <div class="flex min-w-0 flex-col gap-2">
          <div v-if="featured" class="flex items-center gap-2 text-xs font-medium uppercase text-primary">
            <Sparkles class="size-3.5" aria-hidden="true" />
            Trending
          </div>
          <CardTitle class="leading-tight">
            <span class="text-white transition-colors group-hover:text-primary">{{ idea.titleDisplay }}</span>
          </CardTitle>
          <p class="text-sm text-muted-foreground">
            by
            <Link class="pointer-events-auto relative z-20 font-medium text-primary hover:underline" :href="idea.user.routes.show">@{{ idea.user.username }}</Link>
            <span aria-hidden="true"> · </span>
            {{ idea.createdAtForHumans }}
          </p>
        </div>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)]">
          <section class="flex min-w-0 flex-col gap-1">
            <h3 class="text-xs font-semibold uppercase text-primary">Tagline</h3>
            <p class="break-words text-base leading-7 text-foreground [overflow-wrap:anywhere]">
              {{ idea.tagline }}
            </p>
          </section>

          <section class="flex min-w-0 flex-col gap-1">
            <h3 class="text-xs font-semibold uppercase text-primary">Summary</h3>
            <p class="line-clamp-3 break-words text-sm leading-6 text-muted-foreground [overflow-wrap:anywhere]">
              {{ idea.summary }}
            </p>
          </section>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
          <Badge :variant="statusBadgeVariant" class="w-fit">
            {{ idea.statusDisplay }}
          </Badge>
          <Badge v-for="tag in idea.tags" :key="tag" variant="secondary" class="text-xs">
            {{ tag }}
          </Badge>
          <span v-if="idea.repository" class="inline-flex items-center gap-2">
            <GitBranch class="size-4 text-primary" aria-hidden="true" />
            Repository linked
          </span>
        </div>
      </div>

      <aside class="pointer-events-none flex flex-col gap-5 border-t border-border bg-background/14 p-5 lg:border-l lg:border-t-0 sm:p-6">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
          <div class="flex flex-col gap-2 border-b border-border pb-4 last:border-b-0 last:pb-0">
            <span class="flex items-center gap-2 text-xs font-semibold uppercase text-primary">
              <Users class="size-4 text-primary" aria-hidden="true" />
              Supporters
            </span>
            <strong class="text-3xl font-semibold text-white">{{ idea.supportersCount.toLocaleString() }}</strong>
            <span class="text-xs text-muted-foreground">People who want this built.</span>
          </div>
          <div class="flex flex-col gap-2">
            <span class="flex items-center gap-2 text-xs font-semibold uppercase text-primary">
              <MessageSquare class="size-4 text-primary" aria-hidden="true" />
              Collaborators
            </span>
            <strong class="text-3xl font-semibold text-white">{{ idea.approvedApplicationsCount.toLocaleString() }}</strong>
            <span class="text-xs text-muted-foreground">Approved builders already helping.</span>
          </div>
        </div>

        <Button v-if="idea.can.update" :as="Link" :href="idea.routes.dashboard" size="sm" class="pointer-events-auto relative z-20 w-full">
          <GitBranch class="size-4" aria-hidden="true" />
          Manage
        </Button>
      </aside>
    </div>

    <CardHeader v-else :class="headerClass">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex min-w-0 flex-col gap-2">
          <div v-if="featured" class="flex items-center gap-2 text-xs font-medium uppercase text-primary">
            <Sparkles class="size-3.5" aria-hidden="true" />
            Trending
          </div>
          <template v-if="single && !hideTitle">
            <h1 class="text-2xl font-semibold leading-tight text-white">
              {{ idea.titleDisplay }}
            </h1>
          </template>
          <CardTitle v-else-if="!single" class="leading-tight">
            <span class="text-white transition-colors group-hover:text-primary">{{ idea.titleDisplay }}</span>
          </CardTitle>
          <p class="text-xs text-muted-foreground">
            by
            <Link class="pointer-events-auto relative z-20 font-medium text-primary hover:underline" :href="idea.user.routes.show">@{{ idea.user.username }}</Link>
            <span aria-hidden="true"> · </span>
            {{ idea.createdAtForHumans }}
          </p>
        </div>

        <div v-if="idea.can.update || single" class="pointer-events-auto relative z-20 flex shrink-0 flex-wrap items-center gap-2">
          <Badge :variant="statusBadgeVariant" class="w-fit">
            {{ idea.statusDisplay }}
          </Badge>
        </div>
      </div>
    </CardHeader>

    <CardContent v-if="single" class="flex flex-col gap-5">
      <section class="flex flex-col gap-2" aria-label="Idea summary">
        <h2 class="text-sm font-semibold text-white">Summary</h2>
        <p class="break-words text-base leading-7 text-white [overflow-wrap:anywhere]">
          {{ idea.summary }}
        </p>
        <div v-if="idea.tags.length > 0" class="flex flex-wrap gap-2 pt-1">
          <Badge v-for="tag in idea.tags" :key="tag" variant="secondary" class="text-xs">
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
          <span class="h-px flex-1 bg-primary/35 transition-colors group-hover:bg-primary/65" aria-hidden="true" />
          <span class="inline-flex items-center gap-1 text-sm font-semibold">
            {{ pitchToggleLabel }}
            <ChevronDown :class="['size-4 transition-transform duration-200', pitchChevronClass]" aria-hidden="true" />
          </span>
          <span class="h-px flex-1 bg-primary/35 transition-colors group-hover:bg-primary/65" aria-hidden="true" />
        </button>

        <Transition
          @before-enter="beforeDescriptionEnter"
          @enter="enterDescription"
          @leave="leaveDescription"
        >
          <div v-if="isDescriptionExpanded" :id="descriptionId" class="description-reveal-panel">
            <slot name="pitch-toc" />
            <div class="min-h-0 min-w-0 overflow-hidden border-t border-border pt-4">
              <MarkdownContent :html="idea.contentHtml" />
            </div>
          </div>
        </Transition>
      </section>
    </CardContent>

    <CardContent v-else-if="!isDetailed" :class="summaryContentClass">
      <p class="line-clamp-2 break-words text-sm leading-6 text-muted-foreground [overflow-wrap:anywhere]">
        {{ idea.tagline }}
      </p>
      <div v-if="idea.tags.length > 0" class="flex flex-wrap gap-1.5">
        <Badge v-for="tag in idea.tags" :key="tag" variant="secondary" class="text-xs">
          {{ tag }}
        </Badge>
      </div>
    </CardContent>

    <div v-if="!single && !isDetailed" :class="cardStatsClass">
      <span class="inline-flex items-center gap-2">
        <Users class="size-4 text-primary" aria-hidden="true" />
        {{ supportersLabel }}
      </span>
      <span class="text-border" aria-hidden="true">/</span>
      <span class="inline-flex items-center gap-2">
        <MessageSquare class="size-4 text-primary" aria-hidden="true" />
        {{ collaboratorsLabel }}
      </span>
    </div>

    <div v-if="!single && !isDetailed && (idea.can.update || idea.repository)" :class="cardActionsClass">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div v-if="idea.repository" class="inline-flex items-center gap-2 text-sm text-muted-foreground">
          <GitBranch class="size-4 text-primary" aria-hidden="true" />
          Repository linked
        </div>

        <Button v-if="idea.can.update" :as="Link" :href="idea.routes.dashboard" size="sm" class="ml-auto">
          <GitBranch class="size-4" aria-hidden="true" />
          Manage
        </Button>
      </div>
    </div>
  </Card>
</template>
