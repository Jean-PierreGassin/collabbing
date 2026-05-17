<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import PaginationLinks from '@/components/pagination/PaginationLinks.vue';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import type { IdeaComment, Paginator } from '@/types/domain';

defineProps<{
  comments: Paginator<IdeaComment>;
}>();
</script>

<template>
  <Card>
    <CardHeader>
      <h2 class="text-lg font-semibold text-white">Comments</h2>
    </CardHeader>
    <CardContent v-if="comments.items.length > 0" class="flex flex-col gap-3">
      <article v-for="comment in comments.items" :key="comment.id" class="flex flex-col gap-3 rounded-md border border-border bg-background/35 p-4">
        <header class="flex flex-wrap items-center justify-between gap-2 text-sm">
          <a class="font-medium text-primary hover:underline" :href="comment.user.routes.show">
            @{{ comment.user.username }}
          </a>
          <span class="text-muted-foreground">
            Posted {{ comment.createdAtForHumans }}
          </span>
        </header>
        <MarkdownContent :html="comment.contentHtml" />
        <div v-if="comment.wasEdited || comment.can.update" class="flex flex-wrap items-center justify-between gap-3 border-t border-border pt-3">
          <span v-if="comment.wasEdited" class="text-sm text-muted-foreground">Last edited {{ comment.updatedAtForHumans }}</span>
          <span v-else />
          <Button v-if="comment.can.update" as="a" :href="comment.routes.edit" variant="outline" size="sm">Edit</Button>
        </div>
      </article>
    </CardContent>
    <CardContent v-else>
      <p class="text-sm text-muted-foreground">No comments yet.</p>
    </CardContent>
    <div v-if="comments.lastPage > 1" class="border-t border-border px-6 py-4">
      <PaginationLinks :paginator="comments" />
    </div>
  </Card>
</template>
