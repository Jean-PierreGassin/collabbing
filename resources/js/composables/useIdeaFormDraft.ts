import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

export interface IdeaFormDraftValues {
  applicationsClosedNote: string;
  applicationsOpen: string;
  collaborationStage: string;
  communication: string;
  communicationNote: string;
  communicationStyle: string;
  content: string;
  firstContribution: string;
  gettingStartedNotes: string;
  helpWanted: string[];
  helpWantedNote: string;
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
  'collaborationStage',
  'helpWanted',
  'helpWantedNote',
  'firstContribution',
  'applicationsOpen',
  'applicationsClosedNote',
  'communicationStyle',
  'communicationNote',
  'gettingStartedNotes',
  'tags',
  'repositoryName',
  'summary',
  'content',
];

const defaultDraftValues: IdeaFormDraftValues = {
  title: '',
  tagline: '',
  communication: '',
  collaborationStage: '',
  helpWanted: [],
  helpWantedNote: '',
  firstContribution: '',
  applicationsOpen: '1',
  applicationsClosedNote: '',
  communicationStyle: '',
  communicationNote: '',
  gettingStartedNotes: '',
  tags: '',
  repositoryName: '',
  summary: '',
  content: '',
};

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
    collaborationStage: values.collaborationStage,
    helpWanted: [...values.helpWanted],
    helpWantedNote: values.helpWantedNote,
    firstContribution: values.firstContribution,
    applicationsOpen: values.applicationsOpen,
    applicationsClosedNote: values.applicationsClosedNote,
    communicationStyle: values.communicationStyle,
    communicationNote: values.communicationNote,
    gettingStartedNotes: values.gettingStartedNotes,
    tags: values.tags,
    repositoryName: values.repositoryName,
    summary: values.summary,
    content: values.content,
  };
}

function sameValues(first: IdeaFormDraftValues, second: IdeaFormDraftValues): boolean {
  return draftFields.every((field) => sameDraftValue(first[field], second[field]));
}

function sameDraftValue(first: string | string[], second: string | string[]): boolean {
  if (Array.isArray(first) || Array.isArray(second)) {
    if (!Array.isArray(first) || !Array.isArray(second)) {
      return false;
    }

    if (first.length !== second.length) {
      return false;
    }

    return first.every((value, index) => value === second[index]);
  }

  return first === second;
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

    return {
      ...defaultDraftValues,
      title: stringDraftValue(parsed.title),
      tagline: stringDraftValue(parsed.tagline),
      communication: stringDraftValue(parsed.communication),
      collaborationStage: stringDraftValue(parsed.collaborationStage),
      helpWanted: stringArrayDraftValue(parsed.helpWanted),
      helpWantedNote: stringDraftValue(parsed.helpWantedNote),
      firstContribution: stringDraftValue(parsed.firstContribution),
      applicationsOpen: stringDraftValue(parsed.applicationsOpen, defaultDraftValues.applicationsOpen),
      applicationsClosedNote: stringDraftValue(parsed.applicationsClosedNote),
      communicationStyle: stringDraftValue(parsed.communicationStyle),
      communicationNote: stringDraftValue(parsed.communicationNote),
      gettingStartedNotes: stringDraftValue(parsed.gettingStartedNotes),
      tags: stringDraftValue(parsed.tags),
      repositoryName: stringDraftValue(parsed.repositoryName),
      summary: stringDraftValue(parsed.summary),
      content: stringDraftValue(parsed.content),
      savedAt: parsed.savedAt,
    };
  } catch {
    return null;
  }
}

function stringDraftValue(value: unknown, fallback = ''): string {
  return typeof value === 'string' ? value : fallback;
}

function stringArrayDraftValue(value: unknown): string[] {
  if (!Array.isArray(value)) {
    return [];
  }

  return value.filter((item): item is string => typeof item === 'string');
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

    restoreDraftValues(options.values, draft);

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

function restoreDraftValues(values: IdeaFormDraftValues, draft: StoredIdeaFormDraft): void {
  values.title = draft.title;
  values.tagline = draft.tagline;
  values.communication = draft.communication;
  values.collaborationStage = draft.collaborationStage;
  values.helpWanted = [...draft.helpWanted];
  values.helpWantedNote = draft.helpWantedNote;
  values.firstContribution = draft.firstContribution;
  values.applicationsOpen = draft.applicationsOpen;
  values.applicationsClosedNote = draft.applicationsClosedNote;
  values.communicationStyle = draft.communicationStyle;
  values.communicationNote = draft.communicationNote;
  values.gettingStartedNotes = draft.gettingStartedNotes;
  values.tags = draft.tags;
  values.repositoryName = draft.repositoryName;
  values.summary = draft.summary;
  values.content = draft.content;
}
