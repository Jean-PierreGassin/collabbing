<script setup lang="ts">
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import CommentComposer from '@/components/comments/CommentComposer.vue';
import CommentThread from '@/components/comments/CommentThread.vue';
import PaginationLinks from '@/components/pagination/PaginationLinks.vue';
import type { DomainUser, IdeaComment, Paginator } from '@/types/domain';

const props = defineProps<{
  comments: Paginator<IdeaComment>;
  commentsStore: string;
  mentionableUsers: DomainUser[];
}>();

function commentsPanelClass(): string | undefined {
  if (props.comments.items.length === 0) {
    return undefined;
  }

  return 'min-h-[34rem]';
}
</script>

<template>
  <Card>
    <CardHeader class="gap-4">
      <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex flex-col gap-1">
          <h2 class="text-lg font-semibold text-white">Comments</h2>
        </div>
        <PaginationLinks
          v-if="comments.lastPage > 1"
          label="Comment pages"
          :paginator="comments"
          :only="['comments']"
          placement="toolbar"
        />
      </div>
      <CommentComposer
        :action="commentsStore"
        label="Comment"
        hide-label
        :mentionable-users="mentionableUsers"
        textarea-id="comment-content"
      />
    </CardHeader>
    <CardContent :class="commentsPanelClass()">
      <Transition
        name="page-fade"
        mode="out-in">
        <div
          :key="comments.currentPage"
          class="flex flex-col gap-3">
          <template v-if="comments.items.length > 0">
            <CommentThread
              v-for="comment in comments.items"
              :key="comment.id"
              :comment="comment"
              :comments-store="commentsStore"
              :mentionable-users="mentionableUsers"
            />
          </template>
          <p
            v-else
            class="text-sm text-muted-foreground">No comments yet.</p>
        </div>
      </Transition>
    </CardContent>
  </Card>
</template>
