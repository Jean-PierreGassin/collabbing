<script setup lang="ts">
import { ref } from 'vue';
import CommentComposer from '@/components/comments/CommentComposer.vue';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import { Button } from '@/components/ui/button';
import type { DomainUser, IdeaComment } from '@/types/domain';

defineOptions({
  name: 'CommentThread',
});

withDefaults(defineProps<{
  comment: IdeaComment;
  commentsStore: string;
  depth?: number;
  mentionableUsers: DomainUser[];
}>(), {
  depth: 0,
});

const isReplying = ref(false);
const isEditing = ref(false);
const areRepliesVisible = ref(false);

function startReply(): void {
  isReplying.value = true;
  areRepliesVisible.value = true;
}
</script>

<template>
  <article :class="['flex flex-col gap-3 rounded-md border border-border bg-background/35 p-4', depth > 0 ? 'ml-4 border-l-primary/45' : '']">
    <header class="flex flex-wrap items-center justify-between gap-2 text-sm">
      <a class="font-medium text-primary hover:underline" :href="comment.user.routes.show">
        @{{ comment.user.username }}
      </a>
      <span class="text-muted-foreground">
        Posted {{ comment.createdAtForHumans }}
      </span>
    </header>

    <CommentComposer
      v-if="isEditing"
      :action="comment.routes.update"
      autofocus
      button-label="Save changes"
      cancel-label="Cancel"
      :initial-content="comment.content"
      label="Edit comment"
      :mentionable-users="mentionableUsers"
      method="PUT"
      :textarea-id="`edit-comment-content-${comment.id}`"
      @cancel="isEditing = false"
    />
    <MarkdownContent v-else :html="comment.contentHtml" />

    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-border pt-3">
      <span v-if="comment.wasEdited" class="text-sm text-muted-foreground">Last edited {{ comment.updatedAtForHumans }}</span>
      <span v-else />
      <div class="flex flex-wrap gap-2">
        <Button v-if="comment.replies.length > 0" type="button" variant="ghost" size="sm" @click="areRepliesVisible = !areRepliesVisible">
          {{ areRepliesVisible ? 'Hide' : 'Show' }} {{ comment.replies.length.toLocaleString() }} {{ comment.replies.length === 1 ? 'reply' : 'replies' }}
        </Button>
        <Button type="button" variant="ghost" size="sm" @click="isReplying ? isReplying = false : startReply()">
          {{ isReplying ? 'Cancel reply' : 'Reply' }}
        </Button>
        <Button v-if="comment.can.update && !isEditing" type="button" variant="outline" size="sm" @click="isEditing = true">Edit</Button>
      </div>
    </div>

    <CommentComposer
      v-if="isReplying"
      :action="commentsStore"
      button-label="Post reply"
      label="Reply"
      :mentionable-users="mentionableUsers"
      :parent-id="comment.id"
      :placeholder="`Reply to @${comment.user.username}`"
      :textarea-id="`reply-content-${comment.id}`"
    />

    <div v-if="comment.replies.length > 0 && areRepliesVisible" class="flex flex-col gap-3">
      <CommentThread
        v-for="reply in comment.replies"
        :key="reply.id"
        :comment="reply"
        :comments-store="commentsStore"
        :depth="depth + 1"
        :mentionable-users="mentionableUsers"
      />
    </div>
  </article>
</template>
