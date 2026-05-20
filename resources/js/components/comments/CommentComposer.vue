<script setup lang="ts">
import { computed, nextTick, onMounted, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import { Button } from '@/components/ui/button';
import { oldInputString } from '@/lib/forms';
import { maxLengthValidator } from '@/lib/formValidation';
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
}>();

const oldParentId = oldInputString('parent_id');
const shouldUseOldContent = props.method === 'POST' && oldParentId === String(props.parentId ?? '');
const textarea = ref<HTMLTextAreaElement | null>(null);
let initialContent = props.initialContent;

if (shouldUseOldContent) {
  initialContent = oldInputString('content', props.initialContent);
}

const content = ref(initialContent);
const cursorPosition = ref(content.value.length);
const contentValidator = maxLengthValidator(1500, 'a comment');
const form = useForm({
  content: content.value,
  parent_id: props.parentId,
});

const activeMention = computed(() => {
  const beforeCursor = content.value.slice(0, cursorPosition.value);
  const match = beforeCursor.match(/(^|\s)@([A-Za-z0-9_-]{0,20})$/);

  if (! match) {
    return null;
  }

  return {
    query: match[2].toLowerCase(),
    start: beforeCursor.length - match[2].length - 1,
    end: cursorPosition.value,
  };
});

const mentionSuggestions = computed(() => {
  if (! activeMention.value) {
    return [];
  }

  return props.mentionableUsers
    .filter((user) => user.username.toLowerCase().startsWith(activeMention.value?.query ?? ''))
    .slice(0, 5);
});

const hasMentionSuggestions = computed(() => mentionSuggestions.value.length > 0);
const overrideMethod = computed(() => {
  if (props.method === 'POST') {
    return null;
  }

  return props.method;
});

function updateCursorPosition(): void {
  cursorPosition.value = textarea.value?.selectionStart ?? content.value.length;
}

function insertMention(username: string): void {
  const input = textarea.value;
  const mention = `@${username} `;

  if (! input) {
    return;
  }

  const start = activeMention.value?.start ?? input.selectionStart ?? content.value.length;
  const end = activeMention.value?.end ?? input.selectionEnd ?? content.value.length;
  const prefix = content.value.slice(0, start);
  let spacer = ' ';

  if (prefix.length === 0 || /\s$/.test(prefix)) {
    spacer = '';
  }

  content.value = `${prefix}${spacer}${mention}${content.value.slice(end)}`;

  void nextTick(() => {
    const nextPosition = start + spacer.length + mention.length;

    input.setSelectionRange(nextPosition, nextPosition);
    input.focus();
    updateCursorPosition();
  });
}

function insertFirstMentionSuggestion(): void {
  const suggestion = mentionSuggestions.value[0];

  if (! suggestion) {
    return;
  }

  insertMention(suggestion.username);
}

function syncInput(event: Event): void {
  content.value = (event.target as HTMLTextAreaElement).value;
  form.content = content.value;
  updateCursorPosition();
}

function handleMentionTab(event: KeyboardEvent): void {
  if (! hasMentionSuggestions.value) {
    return;
  }

  event.preventDefault();
  insertFirstMentionSuggestion();
}

function focusTextarea(): void {
  if (! props.autofocus) {
    return;
  }

  void nextTick(() => {
    textarea.value?.focus();
  });
}

function submitComment(): void {
  form.content = content.value;
  form.parent_id = props.parentId;

  const options = {
    preserveScroll: true,
    onSuccess: () => {
      if (props.method !== 'POST') {
        return;
      }

      content.value = '';
      form.content = '';
    },
  };

  if (props.method === 'POST') {
    form.post(props.action, options);

    return;
  }

  if (props.method === 'PUT') {
    form.put(props.action, options);

    return;
  }

  form.patch(props.action, options);
}

onMounted(focusTextarea);
</script>

<template>
  <form :action="action" method="POST" class="flex flex-col gap-4" @submit.prevent="submitComment">
    <CsrfField />
    <MethodField v-if="overrideMethod" :method="overrideMethod" />
    <input v-if="parentId" type="hidden" name="parent_id" :value="parentId">
    <FormField :id="textareaId" error-key="content" :label="label" :hide-label="hideLabel" help="Markdown and @mentions are supported." :validator="contentValidator">
      <template #default="{ invalid, describedBy, feedbackClass }">
        <div class="relative">
          <textarea
            :id="textareaId"
            ref="textarea"
            v-model="content"
            name="content"
            :class="['min-h-32 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]"
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
      <Button v-if="cancelLabel" type="button" variant="ghost" size="sm" @click="emit('cancel')">{{ cancelLabel }}</Button>
      <Button type="submit" size="sm" :disabled="form.processing">{{ buttonLabel }}</Button>
    </div>
  </form>
</template>
