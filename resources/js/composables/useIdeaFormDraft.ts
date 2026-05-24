import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

export interface IdeaFormDraftValues {
  communication: string;
  content: string;
  repositoryName: string;
  summary: string;
  tagline: string;
  tags: string;
  title: string;
}

interface StoredIdeaFormDraft extends IdeaFormDraftValues {
  savedAt: string;
}

interface UseIdeaFormDraftOptions {
  allowRestore: boolean;
  initialValues: IdeaFormDraftValues;
  storageKey: string;
  values: IdeaFormDraftValues;
}

const draftFields: (keyof IdeaFormDraftValues)[] = [
  'title',
  'tagline',
  'communication',
  'tags',
  'repositoryName',
  'summary',
  'content',
];

const createDraftKey = 'collabbing.ideaFormDraft.create';

function storage(): Storage | null {
  if (typeof window === 'undefined') {
    return null;
  }

  return window.localStorage;
}

function snapshot(values: IdeaFormDraftValues): IdeaFormDraftValues {
  return {
    title: values.title,
    tagline: values.tagline,
    communication: values.communication,
    tags: values.tags,
    repositoryName: values.repositoryName,
    summary: values.summary,
    content: values.content,
  };
}

function sameValues(first: IdeaFormDraftValues, second: IdeaFormDraftValues): boolean {
  return draftFields.every((field) => first[field] === second[field]);
}

function readDraft(storageKey: string): StoredIdeaFormDraft | null {
  try {
    const storedDraft = storage()?.getItem(storageKey);

    if (!storedDraft) {
      return null;
    }

    const parsed = JSON.parse(storedDraft) as Partial<StoredIdeaFormDraft>;

    if (typeof parsed.savedAt !== 'string') {
      return null;
    }

    const values = draftFields.reduce<Partial<IdeaFormDraftValues>>((draftValues, field) => {
      if (typeof parsed[field] === 'string') {
        draftValues[field] = parsed[field];
      }

      return draftValues;
    }, {});

    if (draftFields.some((field) => typeof values[field] !== 'string')) {
      return null;
    }

    return {
      title: values.title ?? '',
      tagline: values.tagline ?? '',
      communication: values.communication ?? '',
      tags: values.tags ?? '',
      repositoryName: values.repositoryName ?? '',
      summary: values.summary ?? '',
      content: values.content ?? '',
      savedAt: parsed.savedAt,
    };
  } catch {
    return null;
  }
}

function removeDraft(storageKey: string): void {
  try {
    storage()?.removeItem(storageKey);
  } catch {
    // Local draft storage is best-effort; the form should still work without it.
  }
}

export function ideaFormDraftKey(ideaId?: number): string {
  if (ideaId) {
    return `collabbing.ideaFormDraft.edit.${ideaId}`;
  }

  return createDraftKey;
}

export function useIdeaFormDraft(options: UseIdeaFormDraftOptions) {
  const recoverableDraft = ref<StoredIdeaFormDraft | null>(null);
  const savedAt = ref<string | null>(null);
  const isSubmitting = ref(false);
  const initialValues = snapshot(options.initialValues);
  const storedDraft = readDraft(options.storageKey);

  if (storedDraft && sameValues(storedDraft, initialValues)) {
    removeDraft(options.storageKey);
  } else if (storedDraft && options.allowRestore) {
    recoverableDraft.value = storedDraft;
    savedAt.value = storedDraft.savedAt;
  }

  const hasRecoverableDraft = computed(() => recoverableDraft.value !== null);
  const hasUnsavedChanges = computed(() => !sameValues(snapshot(options.values), initialValues));
  const draftSavedAtLabel = computed(() => {
    if (!savedAt.value) {
      return '';
    }

    return new Date(savedAt.value).toLocaleString([], {
      dateStyle: 'medium',
      timeStyle: 'short',
    });
  });

  function writeDraft(): void {
    if (isSubmitting.value) {
      return;
    }

    if (!hasUnsavedChanges.value) {
      removeDraft(options.storageKey);
      savedAt.value = null;

      return;
    }

    const nextSavedAt = new Date().toISOString();
    const draft: StoredIdeaFormDraft = {
      ...snapshot(options.values),
      savedAt: nextSavedAt,
    };

    try {
      storage()?.setItem(options.storageKey, JSON.stringify(draft));
      savedAt.value = nextSavedAt;
    } catch {
      savedAt.value = null;
    }
  }

  function restoreDraft(): void {
    if (!recoverableDraft.value) {
      return;
    }

    const draft = recoverableDraft.value;

    draftFields.forEach((field) => {
      options.values[field] = draft[field];
    });

    recoverableDraft.value = null;
    savedAt.value = draft.savedAt;
    writeDraft();
  }

  function discardDraft(): void {
    recoverableDraft.value = null;
    savedAt.value = null;
    removeDraft(options.storageKey);
  }

  function clearDraft(): void {
    discardDraft();
  }

  function markSubmitting(): void {
    isSubmitting.value = true;
    clearDraft();
  }

  function warnBeforeUnload(event: BeforeUnloadEvent): void {
    if (!hasUnsavedChanges.value || isSubmitting.value) {
      return;
    }

    event.preventDefault();
    event.returnValue = '';
  }

  watch(
    () => snapshot(options.values),
    writeDraft,
    { deep: true },
  );

  onMounted(() => {
    window.addEventListener('beforeunload', warnBeforeUnload);
  });

  onUnmounted(() => {
    window.removeEventListener('beforeunload', warnBeforeUnload);
  });

  return {
    clearDraft,
    discardDraft,
    draftSavedAtLabel,
    hasRecoverableDraft,
    hasUnsavedChanges,
    markSubmitting,
    restoreDraft,
  };
}
