<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { oldInputString } from '@/lib/forms';
import type { Idea, IdeaComment } from '@/types/domain';

defineProps<{
  idea: Idea;
  comment?: IdeaComment;
}>();
</script>

<template>
  <Card>
    <CardHeader><h1 class="text-2xl font-semibold text-white">{{ comment ? 'Edit comment' : 'Comment' }}</h1></CardHeader>
    <CardContent>
      <form :action="comment ? comment.routes.update : idea.routes.commentsStore" method="POST" class="flex flex-col gap-4">
        <CsrfField />
        <MethodField v-if="comment" method="PUT" />
        <FormField id="content" :label="comment ? 'Comment' : 'Content'" help="Keep it specific and constructive. Markdown and @mentions are supported.">
          <template #default="{ invalid, describedBy }">
            <textarea
              id="content"
              name="content"
              class="min-h-40 rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40"
              :placeholder="comment ? undefined : 'Add context, a suggestion, or a useful question.'"
              maxlength="1500"
              :value="oldInputString('content', comment?.content)"
              :aria-invalid="invalid || undefined"
              :aria-describedby="describedBy"
              required
            />
          </template>
        </FormField>
        <Button type="submit" size="sm" :variant="comment ? 'secondary' : 'default'" class="self-start">
          {{ comment ? 'Edit Comment' : 'Share Comment' }}
        </Button>
      </form>
    </CardContent>
  </Card>
</template>
