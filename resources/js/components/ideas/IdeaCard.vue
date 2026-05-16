<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Link } from '@inertiajs/vue3';
import type { Idea } from '@/types/domain';

withDefaults(defineProps<{
  idea: Idea;
  single?: boolean;
  showComments?: boolean;
}>(), {
  single: false,
  showComments: false,
});
</script>

<template>
  <Card>
    <CardHeader>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <CardTitle>
          <template v-if="!single">
            <Link class="text-primary hover:underline" :href="idea.routes.show">{{ idea.titleDisplay }}</Link>
          </template>
          <template v-else>
            {{ idea.titleDisplay }} - <small class="text-sm font-normal text-muted-foreground">by <Link class="text-primary hover:underline" :href="idea.user.routes.show">{{ idea.user.username }}</Link></small>
          </template>
        </CardTitle>

        <div v-if="idea.can.update" class="text-sm text-muted-foreground">
          Status:
          <span :class="idea.status === 'open' ? 'text-primary' : 'text-destructive'">
            {{ idea.statusDisplay }}
          </span>
        </div>
      </div>
    </CardHeader>

    <CardContent v-if="single" class="flex flex-col gap-4">
      <div class="prose prose-invert max-w-none" v-html="idea.contentHtml" />
      <h6 class="text-right text-sm text-muted-foreground"><i>Created {{ idea.createdAtForHumans }}</i></h6>
    </CardContent>

    <div class="flex flex-col gap-3 border-t border-border px-6 py-4">
      <div v-if="idea.can.update" class="flex flex-wrap gap-2">
        <Button :as="Link" :href="idea.routes.edit" variant="secondary" size="sm">Edit Idea</Button>
        <Button :as="Link" :href="idea.routes.dashboard" variant="outline" size="sm">Idea Dashboard</Button>
      </div>

      <div v-if="!single" class="grid gap-2 text-sm sm:grid-cols-2">
        <div>
          Supporters: {{ idea.supportersCount.toLocaleString() }},
          Collaborators: {{ idea.approvedApplicationsCount.toLocaleString() }}
        </div>
        <div class="text-muted-foreground sm:text-right">
          <i>Created {{ idea.createdAtForHumans }}</i>
        </div>
      </div>
    </div>
  </Card>
</template>
