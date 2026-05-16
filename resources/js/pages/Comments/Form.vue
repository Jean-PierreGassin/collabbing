<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import type { Idea, IdeaComment } from '@/types/domain';

defineProps<{
  idea: Idea;
  comment?: IdeaComment;
}>();
</script>

<template>
  <Card>
    <CardHeader>{{ comment ? 'Edit your Comment' : 'Share your Comment' }}</CardHeader>
    <CardContent>
      <form :action="comment ? comment.routes.update : idea.routes.commentsStore" method="POST" class="flex flex-col gap-4">
        <CsrfField />
        <MethodField v-if="comment" method="PUT" />
        <FormField :id="comment ? 'content' : 'content'" :label="comment ? 'Comment' : 'Content'" help="Nobody likes a bossy boots, think before you type.">
          <textarea
            id="content"
            name="content"
            class="min-h-40 rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40"
            :placeholder="comment ? undefined : 'I liked the thing you said about the other thing, however I prefer to do it this way instead'"
            required
          >{{ comment?.content ?? '' }}</textarea>
        </FormField>
        <Button type="submit" size="sm" :variant="comment ? 'secondary' : 'default'" class="self-end">
          {{ comment ? 'Edit Comment' : 'Share Comment' }}
        </Button>
      </form>
    </CardContent>
  </Card>
</template>
