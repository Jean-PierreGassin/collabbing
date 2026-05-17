<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import { Link } from '@inertiajs/vue3';
import { GitBranch, MessageSquare, Sparkles, Users } from '@lucide/vue';
import type { Idea } from '@/types/domain';

const props = withDefaults(defineProps<{
  idea: Idea;
  featured?: boolean;
  single?: boolean;
  showComments?: boolean;
}>(), {
  featured: false,
  single: false,
  showComments: false,
});

const excerpt = computed(() => props.idea.content
  .replace(/[#*_`>\-[\]()]/g, ' ')
  .replace(/\s+/g, ' ')
  .trim()
  .slice(0, props.featured ? 150 : 220));

const collaboratorsLabel = computed(() => {
  const count = props.idea.approvedApplicationsCount;

  return `${count.toLocaleString()} ${count === 1 ? 'collaborator' : 'collaborators'}`;
});

const supportersLabel = computed(() => {
  const count = props.idea.supportersCount;

  return `${count.toLocaleString()} ${count === 1 ? 'supporter' : 'supporters'}`;
});
</script>

<template>
  <Card :class="featured ? 'h-full border-primary/20 bg-card/95' : 'bg-card/90'">
    <CardHeader :class="single ? undefined : 'gap-4'">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex min-w-0 flex-col gap-2">
          <div v-if="featured" class="flex items-center gap-2 text-xs font-medium uppercase text-primary">
            <Sparkles class="size-3.5" aria-hidden="true" />
            Trending
          </div>
          <CardTitle class="leading-tight">
            <template v-if="!single">
              <Link class="text-white transition-colors hover:text-primary" :href="idea.routes.show">{{ idea.titleDisplay }}</Link>
            </template>
            <template v-else>
              {{ idea.titleDisplay }}
            </template>
          </CardTitle>
          <p class="text-sm text-muted-foreground">
            by
            <Link class="font-medium text-primary hover:underline" :href="idea.user.routes.show">@{{ idea.user.username }}</Link>
            <span aria-hidden="true"> · </span>
            {{ idea.createdAtForHumans }}
          </p>
        </div>

        <Badge
          v-if="idea.can.update || single"
          :variant="idea.status === 'open' ? 'default' : 'secondary'"
          class="w-fit"
        >
          {{ idea.statusDisplay }}
        </Badge>
      </div>
    </CardHeader>

    <CardContent v-if="single" class="flex flex-col gap-4">
      <MarkdownContent :html="idea.contentHtml" />
      <div class="grid gap-2 border-t border-border pt-4 text-sm text-muted-foreground sm:grid-cols-2">
        <div class="inline-flex items-center gap-2">
          <Users class="size-4 text-primary" aria-hidden="true" />
          {{ supportersLabel }}
        </div>
        <div class="inline-flex items-center gap-2 sm:justify-end">
          <MessageSquare class="size-4 text-primary" aria-hidden="true" />
          {{ collaboratorsLabel }}
        </div>
      </div>
    </CardContent>

    <CardContent v-else class="flex flex-1 flex-col gap-4">
      <p class="line-clamp-3 text-sm leading-6 text-muted-foreground">
        {{ excerpt }}
      </p>

      <div class="grid gap-2 text-sm text-muted-foreground sm:grid-cols-2">
        <div class="inline-flex items-center gap-2">
          <Users class="size-4 text-primary" aria-hidden="true" />
          {{ supportersLabel }}
        </div>
        <div class="inline-flex items-center gap-2">
          <MessageSquare class="size-4 text-primary" aria-hidden="true" />
          {{ collaboratorsLabel }}
        </div>
      </div>
    </CardContent>

    <div class="flex flex-col gap-3 border-t border-border bg-background/18 px-6 py-4">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
          <template v-if="!single">
            <Button :as="Link" :href="idea.routes.show" variant="outline" size="sm">View idea</Button>
          </template>

          <template v-if="idea.can.update">
            <Button :as="Link" :href="idea.routes.edit" variant="secondary" size="sm">Edit Idea</Button>
            <Button :as="Link" :href="idea.routes.dashboard" variant="outline" size="sm">
              <GitBranch class="size-4" aria-hidden="true" />
              Dashboard
            </Button>
          </template>
        </div>

        <div v-if="idea.repository" class="inline-flex items-center gap-2 text-sm text-muted-foreground">
          <GitBranch class="size-4 text-primary" aria-hidden="true" />
          Repository linked
        </div>
      </div>
    </div>
  </Card>
</template>
