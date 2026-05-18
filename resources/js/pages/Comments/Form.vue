<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { oldInputString } from '@/lib/forms';
import type { Idea, IdeaComment } from '@/types/domain';

const props = defineProps<{
  idea: Idea;
  comment?: IdeaComment;
}>();

let pageTitle = 'Comment';
let formAction = props.idea.routes.commentsStore;
let fieldLabel = 'Content';
let textareaPlaceholder: string | undefined = 'Add context, a suggestion, or a useful question.';
let buttonVariant: 'default' | 'secondary' = 'default';
let buttonLabel = 'Share Comment';

if (props.comment) {
  pageTitle = 'Edit comment';
  formAction = props.comment.routes.update;
  fieldLabel = 'Comment';
  textareaPlaceholder = undefined;
  buttonVariant = 'secondary';
  buttonLabel = 'Edit Comment';
}
</script>

<template>
  <Card>
    <CardHeader><h1 class="text-2xl font-semibold text-white">{{ pageTitle }}</h1></CardHeader>
    <CardContent>
      <form :action="formAction" method="POST" class="flex flex-col gap-4">
        <CsrfField />
        <MethodField v-if="comment" method="PUT" />
        <FormField id="content" :label="fieldLabel" help="Keep it specific and constructive. Markdown and @mentions are supported.">
          <template #default="{ invalid, describedBy }">
            <textarea
              id="content"
              name="content"
              class="min-h-40 rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40"
              :placeholder="textareaPlaceholder"
              maxlength="1500"
              :value="oldInputString('content', comment?.content)"
              :aria-invalid="invalid || undefined"
              :aria-describedby="describedBy"
              required
            />
          </template>
        </FormField>
        <Button type="submit" size="sm" :variant="buttonVariant" class="self-start">
          {{ buttonLabel }}
        </Button>
      </form>
    </CardContent>
  </Card>
</template>
