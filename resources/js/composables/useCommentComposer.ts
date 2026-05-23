import { computed, nextTick, onMounted, ref } from 'vue';
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

  const hasMentionSuggestions = computed(() => mentionSuggestions.value.length > 0);

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

  function handleMentionTab(event: KeyboardEvent): void {
    if (!hasMentionSuggestions.value) {
      return;
    }

    event.preventDefault();
    insertFirstMentionSuggestion();
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

  return {
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
  };
}
