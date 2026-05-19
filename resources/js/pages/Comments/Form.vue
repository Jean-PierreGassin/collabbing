<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { oldInputString } from '@/lib/forms';
import { maxLengthValidator } from '@/lib/formValidation';
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
const contentValidator = maxLengthValidator(1500, 'a comment');

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
  <Card class="w-full max-w-3xl">
    <CardHeader><h1 class="text-2xl font-semibold text-white">{{ pageTitle }}</h1></CardHeader>
    <CardContent>
      <form :action="formAction" method="POST" class="flex flex-col gap-4">
        <CsrfField />
        <MethodField v-if="comment" method="PUT" />
        <FormField id="content" :label="fieldLabel" help="Keep it specific and constructive. Markdown and @mentions are supported." :validator="contentValidator">
          <template #default="{ invalid, describedBy, feedbackClass }">
            <textarea
              id="content"
              name="content"
              :class="['min-h-40 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]"
              :placeholder="textareaPlaceholder"
              maxlength="1500"
              :defaultValue="oldInputString('content', comment?.content)"
              :aria-invalid="invalid || undefined"
              :aria-describedby="describedBy"
              required
            />
          </template>
        </FormField>
        <div class="flex justify-end">
          <Button type="submit" size="sm" :variant="buttonVariant">
            {{ buttonLabel }}
          </Button>
        </div>
      </form>
    </CardContent>
  </Card>
</template>
