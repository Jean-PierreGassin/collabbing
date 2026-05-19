<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue';
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
const activeSuggestionIndex = ref(0);
const isMentionListDismissed = ref(false);

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

const activeMentionKey = computed(() => {
  if (! activeMention.value) {
    return null;
  }

  return [
    activeMention.value.start,
    activeMention.value.end,
    activeMention.value.query,
  ].join(':');
});
const visibleMentionSuggestions = computed(() => {
  if (isMentionListDismissed.value) {
    return [];
  }

  return mentionSuggestions.value;
});
const hasMentionSuggestions = computed(() => visibleMentionSuggestions.value.length > 0);
const mentionListId = computed(() => `${props.textareaId}-mention-suggestions`);
const activeSuggestion = computed(() => visibleMentionSuggestions.value[activeSuggestionIndex.value] ?? null);
const activeSuggestionId = computed(() => {
  if (! activeSuggestion.value) {
    return undefined;
  }

  return `${mentionListId.value}-option-${activeSuggestion.value.id}`;
});
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
  let suffix = content.value.slice(end);
  let spacer = ' ';

  if (prefix.length === 0 || /\s$/.test(prefix)) {
    spacer = '';
  }

  if (/^[ \t]/.test(suffix)) {
    suffix = suffix.replace(/^[ \t]+/, '');
  }

  content.value = `${prefix}${spacer}${mention}${suffix}`;

  void nextTick(() => {
    const nextPosition = start + spacer.length + mention.length;

    input.setSelectionRange(nextPosition, nextPosition);
    input.focus();
    updateCursorPosition();
  });
}

function insertFirstMentionSuggestion(): void {
  const suggestion = activeSuggestion.value ?? visibleMentionSuggestions.value[0];

  if (! suggestion) {
    return;
  }

  insertMention(suggestion.username);
}

function moveActiveSuggestion(direction: 1 | -1): void {
  if (! hasMentionSuggestions.value) {
    return;
  }

  const nextIndex = activeSuggestionIndex.value + direction;
  const suggestionCount = visibleMentionSuggestions.value.length;

  if (nextIndex < 0) {
    activeSuggestionIndex.value = suggestionCount - 1;

    return;
  }

  if (nextIndex >= suggestionCount) {
    activeSuggestionIndex.value = 0;

    return;
  }

  activeSuggestionIndex.value = nextIndex;
}

function syncInput(event: Event): void {
  content.value = (event.target as HTMLTextAreaElement).value;
  updateCursorPosition();
}

function focusTextarea(): void {
  if (! props.autofocus) {
    return;
  }

  void nextTick(() => {
    textarea.value?.focus();
  });
}

function handleMentionKeydown(event: KeyboardEvent): void {
  if (event.key === 'Escape' && hasMentionSuggestions.value) {
    event.preventDefault();
    isMentionListDismissed.value = true;
    activeSuggestionIndex.value = 0;

    return;
  }

  if (event.key === 'ArrowDown' && hasMentionSuggestions.value) {
    event.preventDefault();
    moveActiveSuggestion(1);

    return;
  }

  if (event.key === 'ArrowUp' && hasMentionSuggestions.value) {
    event.preventDefault();
    moveActiveSuggestion(-1);

    return;
  }

  if ((event.key === 'Enter' || event.key === 'Tab') && hasMentionSuggestions.value) {
    event.preventDefault();
    insertFirstMentionSuggestion();
  }
}

watch(activeMentionKey, () => {
  activeSuggestionIndex.value = 0;
  isMentionListDismissed.value = false;
});

watch(visibleMentionSuggestions, (suggestions) => {
  if (activeSuggestionIndex.value < suggestions.length) {
    return;
  }

  activeSuggestionIndex.value = Math.max(suggestions.length - 1, 0);
});

onMounted(focusTextarea);
</script>

<template>
  <form :action="action" method="POST" class="flex flex-col gap-4">
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
            role="combobox"
            aria-autocomplete="list"
            :aria-expanded="hasMentionSuggestions ? 'true' : 'false'"
            :aria-controls="mentionListId"
            :aria-activedescendant="activeSuggestionId"
            :aria-invalid="invalid || undefined"
            :aria-describedby="describedBy"
            required
            @input="syncInput"
            @click="updateCursorPosition"
            @keyup="updateCursorPosition"
            @select="updateCursorPosition"
            @keydown="handleMentionKeydown"
          />

          <ul
            v-if="hasMentionSuggestions"
            :id="mentionListId"
            role="listbox"
            :aria-label="`${label} mention suggestions`"
            class="absolute left-0 right-0 top-full z-20 mt-2 overflow-hidden rounded-md border border-border bg-popover shadow-xl shadow-black/25"
          >
            <li
              v-for="(user, index) in visibleMentionSuggestions"
              :key="user.id"
              :id="`${mentionListId}-option-${user.id}`"
              role="option"
              :aria-selected="index === activeSuggestionIndex"
              :class="[
                'flex w-full cursor-pointer items-center justify-between gap-3 px-3 py-2 text-left text-sm',
                index === activeSuggestionIndex ? 'bg-secondary' : 'hover:bg-secondary',
              ]"
              @pointerdown.prevent="insertMention(user.username)"
              @mousemove="activeSuggestionIndex = index"
            >
              <span class="font-medium text-primary">@{{ user.username }}</span>
              <span class="truncate text-muted-foreground">{{ user.name }}</span>
            </li>
          </ul>
        </div>
      </template>
    </FormField>

    <div class="flex flex-wrap justify-end gap-2">
      <Button v-if="cancelLabel" type="button" variant="ghost" size="sm" @click="emit('cancel')">{{ cancelLabel }}</Button>
      <Button type="submit" size="sm">{{ buttonLabel }}</Button>
    </div>
  </form>
</template>
