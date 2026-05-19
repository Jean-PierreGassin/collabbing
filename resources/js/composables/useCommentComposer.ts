import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { oldInputString } from '@/lib/forms';
import { maxLengthValidator } from '@/lib/formValidation';
import type { DomainUser } from '@/types/domain';

export interface CommentComposerOptions {
  action: string;
  autofocus: boolean;
  initialContent: string;
  mentionableUsers: DomainUser[];
  method: 'POST' | 'PUT' | 'PATCH';
  parentId: number | null;
  textareaId: string;
}

interface CommentComposerEvents {
  (event: 'submitted'): void;
}

export function useCommentComposer(options: CommentComposerOptions, emit: CommentComposerEvents) {
  const oldParentId = oldInputString('parent_id');
  const shouldUseOldContent = options.method === 'POST' && oldParentId === String(options.parentId ?? '');
  const textarea = ref<HTMLTextAreaElement | null>(null);
  let initialContent = options.initialContent;

  if (shouldUseOldContent) {
    initialContent = oldInputString('content', options.initialContent);
  }

  const content = ref(initialContent);
  const cursorPosition = ref(content.value.length);
  const contentValidator = maxLengthValidator(1500, 'a comment');
  const activeSuggestionIndex = ref(0);
  const isMentionListDismissed = ref(false);
  const form = useForm({
    content: content.value,
    parent_id: options.parentId,
  });

  const activeMention = computed(() => {
    const beforeCursor = content.value.slice(0, cursorPosition.value);
    const match = beforeCursor.match(/(^|\s)@([A-Za-z0-9_-]{0,20})$/);

    if (!match) {
      return null;
    }

    return {
      query: match[2].toLowerCase(),
      start: beforeCursor.length - match[2].length - 1,
      end: cursorPosition.value,
    };
  });

  const mentionSuggestions = computed(() => {
    if (!activeMention.value) {
      return [];
    }

    return options.mentionableUsers
      .filter((user) => user.username.toLowerCase().startsWith(activeMention.value?.query ?? ''))
      .slice(0, 5);
  });

  const activeMentionKey = computed(() => {
    if (!activeMention.value) {
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
  const mentionListId = computed(() => `${options.textareaId}-mention-suggestions`);
  const activeSuggestion = computed(() => visibleMentionSuggestions.value[activeSuggestionIndex.value] ?? null);
  const activeSuggestionId = computed(() => {
    if (!activeSuggestion.value) {
      return undefined;
    }

    return `${mentionListId.value}-option-${activeSuggestion.value.id}`;
  });

  const overrideMethod = computed(() => {
    if (options.method === 'POST') {
      return null;
    }

    return options.method;
  });

  function updateCursorPosition(): void {
    cursorPosition.value = textarea.value?.selectionStart ?? content.value.length;
  }

  function insertMention(username: string): void {
    const input = textarea.value;
    const mention = `@${username} `;

    if (!input) {
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
    form.content = content.value;

    void nextTick(() => {
      const nextPosition = start + spacer.length + mention.length;

      input.setSelectionRange(nextPosition, nextPosition);
      input.focus();
      updateCursorPosition();
    });
  }

  function insertFirstMentionSuggestion(): void {
    const suggestion = activeSuggestion.value ?? visibleMentionSuggestions.value[0];

    if (!suggestion) {
      return;
    }

    insertMention(suggestion.username);
  }

  function syncInput(event: Event): void {
    content.value = (event.target as HTMLTextAreaElement).value;
    form.content = content.value;
    updateCursorPosition();
  }

  function moveActiveSuggestion(direction: 1 | -1): void {
    if (!hasMentionSuggestions.value) {
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

  function focusTextarea(): void {
    if (!options.autofocus) {
      return;
    }

    void nextTick(() => {
      textarea.value?.focus();
    });
  }

  function submitComment(): void {
    form.content = content.value;
    form.parent_id = options.parentId;

    const requestOptions = {
      preserveScroll: true,
      onSuccess: () => {
        emit('submitted');

        if (options.method !== 'POST') {
          return;
        }

        content.value = '';
        form.content = '';
      },
    };

    if (options.method === 'POST') {
      form.post(options.action, requestOptions);

      return;
    }

    if (options.method === 'PUT') {
      form.put(options.action, requestOptions);

      return;
    }

    form.patch(options.action, requestOptions);
  }

  onMounted(focusTextarea);

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

  return {
    activeSuggestionId,
    activeSuggestionIndex,
    content,
    contentValidator,
    form,
    handleMentionKeydown,
    hasMentionSuggestions,
    insertMention,
    mentionListId,
    mentionSuggestions,
    overrideMethod,
    submitComment,
    syncInput,
    textarea,
    updateCursorPosition,
    visibleMentionSuggestions,
  };
}
