<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import { Button } from '@/components/ui/button';
import { useCommentComposer } from '@/composables/useCommentComposer';
import type { DomainUser } from '@/types/domain';

const props = withDefaults(defineProps<{
  action: string;
  buttonLabel?: string;
  cancelLabel?: string | null;
  autofocus?: boolean;
  hideLabel?: boolean;
  initialContent?: string;
  label?: string;
  mentionableUsers?: DomainUser[];
  method?: 'POST' | 'PUT' | 'PATCH';
  parentId?: number | null;
  placeholder?: string;
  textareaId: string;
}>(), {
  buttonLabel: 'Post comment',
  cancelLabel: null,
  autofocus: false,
  hideLabel: false,
  initialContent: '',
  label: 'Comment',
  mentionableUsers: () => [],
  method: 'POST',
  parentId: null,
  placeholder: 'Add context, a suggestion, or a useful question.',
});

const emit = defineEmits<{
  cancel: [];
  submitted: [];
}>();

const {
  content,
  contentValidator,
  form,
  handleMentionTab,
  hasMentionSuggestions,
  insertMention,
  mentionSuggestions,
  overrideMethod,
  submitComment,
  syncInput,
  textarea,
  updateCursorPosition,
} = useCommentComposer(props, emit);
</script>

<template>
  <form
    :action="action"
    method="POST"
    class="flex flex-col gap-4"
    @submit.prevent="submitComment">
    <CsrfField />
    <MethodField
      v-if="overrideMethod"
      :method="overrideMethod" />
    <input
      v-if="parentId"
      type="hidden"
      name="parent_id"
      :value="parentId">
    <FormField
      :id="textareaId"
      error-key="content"
      :label="label"
      :hide-label="hideLabel"
      help="Markdown and @mentions are supported."
      :validator="contentValidator">
      <template #default="{ invalid, describedBy, feedbackClass }">
        <div class="relative">
          <textarea
            :id="textareaId"
            ref="textarea"
            v-model="content"
            name="content"
            :class="[
              'min-h-32 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
              feedbackClass,
            ]"
            :placeholder="placeholder"
            maxlength="1500"
            :aria-invalid="invalid || undefined"
            :aria-describedby="describedBy"
            required
            @input="syncInput"
            @click="updateCursorPosition"
            @keyup="updateCursorPosition"
            @select="updateCursorPosition"
            @keydown.tab="handleMentionTab"
          />

          <div
            v-if="hasMentionSuggestions"
            class="absolute left-0 right-0 top-full z-20 mt-2 overflow-hidden rounded-md border border-border bg-popover shadow-xl shadow-black/25"
          >
            <button
              v-for="user in mentionSuggestions"
              :key="user.id"
              type="button"
              class="flex w-full items-center justify-between gap-3 px-3 py-2 text-left text-sm hover:bg-secondary focus:bg-secondary focus:outline-none"
              @mousedown.prevent="insertMention(user.username)"
            >
              <span class="font-medium text-primary">@{{ user.username }}</span>
              <span class="truncate text-muted-foreground">{{ user.name }}</span>
            </button>
            <div class="border-t border-border px-3 py-2 text-xs text-muted-foreground">
              Press Tab to insert the first mention.
            </div>
          </div>
        </div>
      </template>
    </FormField>

    <div class="flex flex-wrap justify-end gap-2">
      <Button
        v-if="cancelLabel"
        type="button"
        variant="ghost"
        size="sm"
        @click="emit('cancel')">
        {{ cancelLabel }}
      </Button>
      <Button
        type="submit"
        size="sm"
        :disabled="form.processing">
        {{ buttonLabel }}
      </Button>
    </div>
  </form>
</template>
