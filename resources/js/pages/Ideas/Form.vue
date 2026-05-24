<script setup lang="ts">
import { computed, reactive, ref, toRef } from 'vue';
import { Check, Circle, Upload } from '@lucide/vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import FormSelect from '@/components/forms/FormSelect.vue';
import MethodField from '@/components/forms/MethodField.vue';
import PageHeader from '@/components/navigation/PageHeader.vue';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import MarkdownTableOfContents from '@/components/typography/MarkdownTableOfContents.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { ideaFormDraftKey, useIdeaFormDraft, type IdeaFormDraftValues } from '@/composables/useIdeaFormDraft';
import { useIdeaPitchTools } from '@/composables/useIdeaPitchTools';
import { hasOldInput, oldInputBoolean, oldInputString, oldInputStringArray } from '@/lib/forms';
import { maxLengthValidator, repositoryNameValidator } from '@/lib/formValidation';
import { stripGeneratedTableOfContents } from '@/lib/markdown';
import { useSessionStore } from '@/stores/session';
import type { Idea } from '@/types/domain';

const props = defineProps<{
  idea?: Idea;
}>();

const session = useSessionStore();
const form = ref<HTMLFormElement | null>(null);
const contentInput = ref<HTMLTextAreaElement | null>(null);
const markdownFileInput = ref<HTMLInputElement | null>(null);
const notifyCollaborators = ref(oldInputBoolean('notify_collaborators'));
const repositoryNamePattern = '[A-Za-z0-9_-]+';
const repositoryNameAllowedCharacters = /^[A-Za-z0-9_-]+$/;
const repositoryNameSanitizer = /[^A-Za-z0-9_-]/g;
const titleValidator = maxLengthValidator(100, 'a title');
const taglineValidator = maxLengthValidator(60, 'a tagline');
const helpWantedNoteValidator = maxLengthValidator(240, 'a help note');
const firstContributionValidator = maxLengthValidator(1200, 'a first contribution');
const applicationsClosedNoteValidator = maxLengthValidator(240, 'an applications note');
const communicationNoteValidator = maxLengthValidator(240, 'a communication note');
const gettingStartedNotesValidator = maxLengthValidator(10000, 'getting-started notes');
const summaryValidator = maxLengthValidator(240, 'a summary');
const tagsValidator = maxLengthValidator(240, 'tags');
const contentValidator = maxLengthValidator(20000, 'a pitch');
const tagsText = computed(() => props.idea?.tags.join(', ') ?? '');
const previewDescriptionId = 'idea-form-preview-description';
const isEditing = computed(() => Boolean(props.idea));
const currentStep = ref<'basics' | 'collaboration'>(stepFromOldInput());
const isBasicsStep = computed(() => isEditing.value || currentStep.value === 'basics');
const isCollaborationStep = computed(() => isEditing.value || currentStep.value === 'collaboration');
const applicationStatusOptions = [
  {
    label: 'Applications open',
    value: '1',
  },
  {
    label: 'Applications closed',
    value: '0',
  },
];
const collaborationStageOptions = [
  {
    label: 'Not sure yet',
    value: '',
  },
  {
    label: 'Rough idea',
    value: 'rough_idea',
  },
  {
    label: 'Needs shaping',
    value: 'needs_shaping',
  },
  {
    label: 'Ready to build',
    value: 'ready_to_build',
  },
  {
    label: 'Actively building',
    value: 'actively_building',
  },
  {
    label: 'Live',
    value: 'live',
  },
];
const helpAreaOptions = [
  {
    label: 'Frontend',
    value: 'frontend',
  },
  {
    label: 'Backend',
    value: 'backend',
  },
  {
    label: 'Design',
    value: 'design',
  },
  {
    label: 'Product',
    value: 'product',
  },
  {
    label: 'Testing',
    value: 'testing',
  },
  {
    label: 'DevOps',
    value: 'devops',
  },
  {
    label: 'Writing',
    value: 'writing',
  },
  {
    label: 'Research',
    value: 'research',
  },
  {
    label: 'Feedback',
    value: 'feedback',
  },
  {
    label: 'Marketing',
    value: 'marketing',
  },
  {
    label: 'Open to anything',
    value: 'anything',
  },
];
const communicationStyleOptions = [
  {
    label: 'Not decided yet',
    value: '',
  },
  {
    label: 'GitHub',
    value: 'github',
  },
  {
    label: 'Discord',
    value: 'discord',
  },
  {
    label: 'Slack',
    value: 'slack',
  },
  {
    label: 'Email',
    value: 'email',
  },
  {
    label: 'Calls',
    value: 'calls',
  },
];
const ideaFormDraftFields = [
  'title',
  'tagline',
  'communication',
  'collaboration_stage',
  'help_wanted',
  'help_wanted_note',
  'first_contribution',
  'applications_open',
  'applications_closed_note',
  'communication_style',
  'communication_note',
  'getting_started_notes',
  'tags',
  'repository_name',
  'summary',
  'content',
  'status',
];
const formValues = reactive<IdeaFormDraftValues>({
  title: oldInputString('title', props.idea?.title),
  tagline: oldInputString('tagline', props.idea?.tagline),
  communication: oldInputString('communication', props.idea?.communication),
  collaborationStage: oldInputString('collaboration_stage', props.idea?.collaboration.stage),
  helpWanted: oldInputStringArray('help_wanted', props.idea?.collaboration.helpWanted ?? []),
  helpWantedNote: oldInputString('help_wanted_note', props.idea?.collaboration.helpWantedNote),
  firstContribution: oldInputString('first_contribution', props.idea?.collaboration.firstContribution),
  applicationsOpen: oldInputString('applications_open', props.idea?.collaboration.applicationsOpen === false ? '0' : '1'),
  applicationsClosedNote: oldInputString('applications_closed_note', props.idea?.collaboration.applicationsClosedNote),
  communicationStyle: oldInputString('communication_style', props.idea?.collaboration.communicationStyle),
  communicationNote: oldInputString('communication_note', props.idea?.collaboration.communicationNote),
  gettingStartedNotes: oldInputString('getting_started_notes', props.idea?.collaboration.gettingStartedNotes),
  tags: oldInputString('tags', tagsText.value),
  repositoryName: oldInputString('repository_name', props.idea?.repositoryName),
  summary: oldInputString('summary', props.idea?.summary),
  content: stripGeneratedTableOfContents(oldInputString('content', props.idea?.content)),
});
const content = toRef(formValues, 'content');
const legacyCommunicationValue = computed(() => formValues.communicationStyle || formValues.communication || 'Not decided yet');
const {
  discardDraft,
  draftSavedAtLabel,
  hasRecoverableDraft,
  hasUnsavedChanges,
  markSubmitting,
  restoreDraft,
} = useIdeaFormDraft({
  allowRestore: !hasOldInput(ideaFormDraftFields),
  initialValues: { ...formValues },
  storageKey: ideaFormDraftKey(props.idea?.id),
  values: formValues,
});
const {
  contentBody,
  dropMarkdownFiles,
  insertWritingSection,
  markdownImportFeedback,
  markdownImportFeedbackClass,
  openMarkdownFilePicker,
  previewHeadings,
  previewHtml,
  selectMarkdownFiles,
  writingSections,
} = useIdeaPitchTools(content, contentInput, markdownFileInput);

let pageTitle = 'Share your idea';
let formAction = session.routes.ideasStore;
let submitLabel = 'Share Idea';

if (props.idea) {
  pageTitle = 'Edit your idea';
  formAction = props.idea.routes.update;
  submitLabel = 'Edit Idea';
}

const readinessItems = computed(() => [
  {
    complete: formValues.collaborationStage !== '',
    label: 'Set where the idea is at',
  },
  {
    complete: formValues.helpWanted.length > 0 || formValues.helpWantedNote.trim() !== '',
    label: 'Choose the help you want',
  },
  {
    complete: formValues.firstContribution.trim() !== '',
    label: 'Add a first thing someone can do',
  },
  {
    complete: formValues.communicationStyle !== '' || formValues.communicationNote.trim() !== '',
    label: 'Choose how you prefer to coordinate',
  },
  {
    complete: formValues.applicationsOpen !== '',
    label: 'Decide whether applications are open',
  },
  {
    complete: formValues.gettingStartedNotes.trim() !== '',
    label: 'Write private start notes for accepted collaborators',
  },
  {
    complete: formValues.repositoryName.trim() !== '',
    label: 'Create or connect a repository',
  },
]);

function stepFromOldInput(): 'basics' | 'collaboration' {
  if (props.idea) {
    return 'basics';
  }

  if ([
    'title',
    'tagline',
    'summary',
    'tags',
    'content',
  ].some((key) => session.errors[key])) {
    return 'basics';
  }

  if (hasOldInput([
    'collaboration_stage',
    'help_wanted',
    'help_wanted_note',
    'first_contribution',
    'applications_open',
    'applications_closed_note',
    'communication_style',
    'communication_note',
    'getting_started_notes',
    'repository_name',
  ])) {
    return 'collaboration';
  }

  return 'basics';
}

function continueToCollaboration(): void {
  if (!form.value?.reportValidity()) {
    return;
  }

  currentStep.value = 'collaboration';
}

function returnToBasics(): void {
  currentStep.value = 'basics';
}

function sanitizeRepositoryName(event: Event): void {
  const input = event.target as HTMLInputElement;
  const sanitized = input.value.replace(repositoryNameSanitizer, '');

  if (input.value !== sanitized) {
    input.value = sanitized;
    formValues.repositoryName = sanitized;
  }
}

function blockInvalidRepositoryNameInput(event: InputEvent): void {
  if (!event.data || repositoryNameAllowedCharacters.test(event.data)) {
    return;
  }

  event.preventDefault();
}

function pasteRepositoryName(event: ClipboardEvent): void {
  const pasted = event.clipboardData?.getData('text') ?? '';
  const sanitized = pasted.replace(repositoryNameSanitizer, '');

  if (pasted === sanitized) {
    return;
  }

  event.preventDefault();

  const input = event.target as HTMLInputElement;
  const start = input.selectionStart ?? input.value.length;
  const end = input.selectionEnd ?? input.value.length;

  input.value = `${input.value.slice(0, start)}${sanitized}${input.value.slice(end)}`.slice(0, 100);
  input.dispatchEvent(new Event('input', { bubbles: true }));
}

function submitForm(): void {
  markSubmitting();
}
</script>

<template>
  <section class="flex flex-col gap-5">
    <PageHeader
      :title="pageTitle"
      description="Describe the problem, the collaboration shape, and the repository plan."
    />

    <Card class="overflow-visible">
      <CardContent>
        <form
          ref="form"
          :action="formAction"
          method="POST"
          class="flex flex-col gap-5"
          @submit="submitForm">
          <CsrfField />
          <MethodField
            v-if="idea"
            method="PUT" />
          <input
            type="hidden"
            name="communication"
            :value="legacyCommunicationValue">

          <div
            v-if="hasRecoverableDraft"
            class="flex flex-col gap-3 rounded-md border border-amber-400/45 bg-amber-500/10 p-4 text-sm text-amber-950 dark:text-amber-100 sm:flex-row sm:items-center sm:justify-between"
            role="status"
            aria-live="polite">
            <div class="flex flex-col gap-1">
              <p class="font-medium text-foreground">
                Unsaved draft found
              </p>
              <p class="text-muted-foreground">
                Restore your local draft from {{ draftSavedAtLabel || 'earlier' }}, or discard it and keep this version.
              </p>
            </div>
            <div class="flex flex-wrap gap-2">
              <Button
                type="button"
                variant="outline"
                size="sm"
                @click="restoreDraft">
                Restore draft
              </Button>
              <Button
                type="button"
                variant="ghost"
                size="sm"
                @click="discardDraft">
                Discard
              </Button>
            </div>
          </div>

          <p
            v-else-if="hasUnsavedChanges"
            class="rounded-md border border-border bg-background/45 px-3 py-2 text-sm text-muted-foreground"
            role="status"
            aria-live="polite">
            Draft saved locally. You will be warned before leaving with unsaved changes.
          </p>

          <ol
            v-if="!idea"
            class="grid gap-2 rounded-md border border-border bg-background/35 p-2 text-sm sm:grid-cols-2"
            aria-label="Idea creation steps">
            <li>
              <button
                type="button"
                class="flex w-full items-center gap-3 rounded-sm px-3 py-2 text-left transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
                :class="currentStep === 'basics' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-secondary'"
                :aria-current="currentStep === 'basics' ? 'step' : undefined"
                @click="returnToBasics">
                <span class="inline-flex size-6 items-center justify-center rounded-full border border-current text-xs font-semibold">1</span>
                <span class="font-medium">Idea basics</span>
              </button>
            </li>
            <li>
              <button
                type="button"
                class="flex w-full items-center gap-3 rounded-sm px-3 py-2 text-left transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
                :class="currentStep === 'collaboration' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-secondary'"
                :aria-current="currentStep === 'collaboration' ? 'step' : undefined"
                @click="continueToCollaboration">
                <span class="inline-flex size-6 items-center justify-center rounded-full border border-current text-xs font-semibold">2</span>
                <span class="font-medium">Collaboration setup</span>
              </button>
            </li>
          </ol>

          <section
            id="idea-form-basics-step"
            class="flex flex-col gap-5"
            :class="{ hidden: !isBasicsStep }">
            <div
              v-if="idea"
              class="flex flex-col gap-1 border-b border-border pb-3">
              <h2 class="text-base font-semibold text-foreground">
                Idea basics
              </h2>
              <p class="text-sm text-muted-foreground">
                Keep the public pitch and discovery details easy to scan.
              </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
              <FormField
                id="title"
                label="Title"
                :validator="titleValidator">
                <template #default="{ invalid, describedBy, feedbackClass }">
                  <input
                    id="title"
                    v-model="formValues.title"
                    name="title"
                    type="text"
                    :class="[
                      'h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                      feedbackClass,
                    ]"
                    placeholder="A faster way to match design reviewers"
                    maxlength="100"
                    :aria-invalid="invalid || undefined"
                    :aria-describedby="describedBy"
                    :required="isBasicsStep">
                </template>
              </FormField>

              <FormField
                id="tagline"
                label="Tagline"
                help="Short card copy for scanning the ideas list."
                :validator="taglineValidator">
                <template #default="{ invalid, describedBy, feedbackClass }">
                  <input
                    id="tagline"
                    v-model="formValues.tagline"
                    name="tagline"
                    type="text"
                    :class="[
                      'h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                      feedbackClass,
                    ]"
                    placeholder="Match reviewers with focused feedback"
                    maxlength="60"
                    :aria-invalid="invalid || undefined"
                    :aria-describedby="describedBy"
                    :required="isBasicsStep">
                </template>
              </FormField>

              <FormField
                id="tags"
                label="Tags"
                help="Separate tags with commas."
                :validator="tagsValidator">
                <template #default="{ invalid, describedBy, feedbackClass }">
                  <input
                    id="tags"
                    v-model="formValues.tags"
                    name="tags"
                    type="text"
                    :class="[
                      'h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                      feedbackClass,
                    ]"
                    placeholder="design, review, workflow"
                    maxlength="240"
                    autocomplete="off"
                    :aria-invalid="invalid || undefined"
                    :aria-describedby="describedBy">
                </template>
              </FormField>
              <label
                v-if="isEditing && (idea?.approvedApplicationsCount ?? 0) > 0"
                class="flex items-start gap-2 rounded-md border border-border bg-background/35 px-3 py-2 text-sm text-muted-foreground">
                <input
                  v-model="notifyCollaborators"
                  type="checkbox"
                  name="notify_collaborators"
                  value="1"
                  class="mt-1 size-4 rounded border-input bg-background">
                <span>Notify accepted collaborators that private start notes changed.</span>
              </label>
            </div>

            <FormField
              id="summary"
              label="Summary"
              help="Plain text only. This appears on the idea page."
              :validator="summaryValidator">
              <template #default="{ invalid, describedBy, feedbackClass }">
                <textarea
                  id="summary"
                  v-model="formValues.summary"
                  name="summary"
                  :class="[
                    'min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                    feedbackClass,
                  ]"
                  placeholder="A short plain-text overview of who this helps and why it should exist."
                  maxlength="240"
                  :aria-invalid="invalid || undefined"
                  :aria-describedby="describedBy"
                  :required="isBasicsStep" />
              </template>
            </FormField>

            <FormField
              id="content"
              label="Pitch (supports markdown)"
              help="Drop in markdown files to append a generated table of contents and sectioned notes."
              :validator="contentValidator">
              <template #default="{ invalid, describedBy, feedbackClass }">
                <div class="flex flex-col gap-4">
                  <div class="flex flex-col gap-3">
                    <div
                      class="flex flex-wrap gap-2"
                      role="toolbar"
                      aria-label="Pitch section inserts">
                      <div
                        v-for="section in writingSections"
                        :key="section.label"
                        class="group relative">
                        <Button
                          type="button"
                          variant="outline"
                          size="icon"
                          class="size-9"
                          :aria-label="`Insert ${section.label} section`"
                          @mousedown.prevent
                          @click="insertWritingSection(section)"
                        >
                          <component
                            :is="section.icon"
                            class="size-4 text-primary"
                            aria-hidden="true" />
                        </Button>
                        <span class="pointer-events-none absolute bottom-full left-1/2 z-30 mb-2 w-52 -translate-x-1/2 rounded-md border border-border bg-popover px-3 py-2 text-xs leading-5 text-popover-foreground opacity-0 shadow-xl shadow-black/20 transition-opacity duration-150 group-focus-within:opacity-100 group-hover:opacity-100">
                          <span class="block font-semibold text-primary">{{ section.label }}</span>
                          {{ section.description }}
                        </span>
                      </div>
                    </div>
                    <textarea
                      id="content"
                      ref="contentInput"
                      v-model="content"
                      name="content"
                      :class="[
                        'min-h-[28rem] w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                        feedbackClass,
                      ]"
                      placeholder="Explain the problem, who it helps, and what a first version should do."
                      maxlength="20000"
                      :aria-invalid="invalid || undefined"
                      :aria-describedby="describedBy"
                      :required="isBasicsStep"
                    />
                    <div
                      class="flex flex-col gap-3 rounded-md border border-dashed border-border bg-background/35 p-4 text-sm text-muted-foreground"
                      @dragover.prevent
                      @drop.prevent="dropMarkdownFiles"
                    >
                      <span class="font-medium text-foreground">Import markdown files</span>
                      <div class="pt-1">
                        <input
                          id="content_markdown_files"
                          ref="markdownFileInput"
                          type="file"
                          class="sr-only"
                          accept=".md,.markdown,text/markdown,text/plain"
                          multiple
                          @change="selectMarkdownFiles"
                        >
                        <Button
                          type="button"
                          variant="outline"
                          size="sm"
                          class="w-fit"
                          aria-controls="content_markdown_files"
                          @click="openMarkdownFilePicker"
                        >
                          <Upload
                            class="size-4 text-primary"
                            aria-hidden="true" />
                          Choose markdown files
                        </Button>
                      </div>
                      <p
                        v-if="markdownImportFeedback"
                        class="text-xs"
                        :class="markdownImportFeedbackClass"
                        role="status"
                        aria-live="polite"
                      >
                        {{ markdownImportFeedback }}
                      </p>
                    </div>
                  </div>
                  <div class="relative flex min-w-0 flex-col gap-3">
                    <MarkdownTableOfContents
                      :content-id="previewDescriptionId"
                      :headings="previewHeadings"
                      nav-label="Preview table of contents"
                    />

                    <section
                      :id="previewDescriptionId"
                      class="flex min-h-[28rem] min-w-0 flex-col gap-3 rounded-md border border-border bg-background/35 p-4"
                      aria-label="Markdown preview">
                      <div class="flex items-center justify-between gap-3 border-b border-border pb-3">
                        <h2 class="text-sm font-semibold text-white">
                          Preview
                        </h2>
                        <span class="text-xs text-muted-foreground">{{ contentBody.length.toLocaleString() }} / 20,000</span>
                      </div>
                      <div class="min-w-0">
                        <MarkdownContent
                          v-if="contentBody.trim()"
                          :html="previewHtml" />
                        <p
                          v-else
                          class="text-sm text-muted-foreground">
                          Start writing to preview the markdown here.
                        </p>
                      </div>
                    </section>
                  </div>
                </div>
              </template>
            </FormField>
          </section>

          <section
            id="idea-form-collaboration-step"
            class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]"
            :class="{ hidden: !isCollaborationStep }">
            <div class="flex min-w-0 flex-col gap-5">
              <div
                v-if="idea"
                class="flex flex-col gap-1 border-b border-border pb-3">
                <h2 class="text-base font-semibold text-foreground">
                  Collaboration setup
                </h2>
                <p class="text-sm text-muted-foreground">
                  Help people understand how to join and what happens after approval.
                </p>
              </div>

              <div class="grid gap-4 md:grid-cols-2">
                <FormField
                  id="collaboration_stage"
                  label="Collaboration stage">
                  <template #default="{ describedBy, feedbackClass }">
                    <FormSelect
                      id="collaboration_stage"
                      v-model="formValues.collaborationStage"
                      name="collaboration_stage"
                      :class="feedbackClass"
                      :aria-describedby="describedBy">
                      <option
                        v-for="option in collaborationStageOptions"
                        :key="option.value"
                        :value="option.value">
                        {{ option.label }}
                      </option>
                    </FormSelect>
                  </template>
                </FormField>

                <FormField
                  id="applications_open"
                  label="Applications">
                  <template #default="{ describedBy, feedbackClass }">
                    <FormSelect
                      id="applications_open"
                      v-model="formValues.applicationsOpen"
                      name="applications_open"
                      :class="feedbackClass"
                      :aria-describedby="describedBy">
                      <option
                        v-for="option in applicationStatusOptions"
                        :key="option.value"
                        :value="option.value">
                        {{ option.label }}
                      </option>
                    </FormSelect>
                  </template>
                </FormField>
              </div>

              <FormField
                id="help_wanted"
                label="Help wanted"
                help="Choose the areas where help would be useful. People can still apply with other contribution types.">
                <template #default="{ describedBy }">
                  <div
                    id="help_wanted"
                    class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3"
                    :aria-describedby="describedBy">
                    <label
                      v-for="option in helpAreaOptions"
                      :key="option.value"
                      class="flex min-h-10 cursor-pointer items-center gap-2 rounded-md border border-input bg-background/70 px-3 py-2 text-sm transition-colors hover:border-primary/50 hover:bg-secondary/70">
                      <input
                        v-model="formValues.helpWanted"
                        type="checkbox"
                        name="help_wanted[]"
                        :value="option.value"
                        class="size-4 rounded border-input text-primary focus:ring-ring">
                      <span>{{ option.label }}</span>
                    </label>
                  </div>
                </template>
              </FormField>

              <FormField
                id="help_wanted_note"
                label="Help note"
                help="Short plain text for anything the selected areas do not explain."
                :validator="helpWantedNoteValidator">
                <template #default="{ invalid, describedBy, feedbackClass }">
                  <textarea
                    id="help_wanted_note"
                    v-model="formValues.helpWantedNote"
                    name="help_wanted_note"
                    :class="[
                      'min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                      feedbackClass,
                    ]"
                    placeholder="A second pair of eyes on scope and launch copy would help."
                    maxlength="240"
                    :aria-invalid="invalid || undefined"
                    :aria-describedby="describedBy" />
                </template>
              </FormField>

              <FormField
                id="first_contribution"
                label="First contribution"
                help="Public prompt with links and line breaks. Keep it small and concrete."
                :validator="firstContributionValidator">
                <template #default="{ invalid, describedBy, feedbackClass }">
                  <textarea
                    id="first_contribution"
                    v-model="formValues.firstContribution"
                    name="first_contribution"
                    :class="[
                      'min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                      feedbackClass,
                    ]"
                    placeholder="Pick one small issue from the README and suggest the first pull request."
                    maxlength="1200"
                    :aria-invalid="invalid || undefined"
                    :aria-describedby="describedBy" />
                </template>
              </FormField>

              <div class="grid gap-4 md:grid-cols-2">
                <FormField
                  id="communication_style"
                  label="Communication style">
                  <template #default="{ describedBy, feedbackClass }">
                    <FormSelect
                      id="communication_style"
                      v-model="formValues.communicationStyle"
                      name="communication_style"
                      :class="feedbackClass"
                      :aria-describedby="describedBy">
                      <option
                        v-for="option in communicationStyleOptions"
                        :key="option.value"
                        :value="option.value">
                        {{ option.label }}
                      </option>
                    </FormSelect>
                  </template>
                </FormField>

                <FormField
                  id="communication_note"
                  label="Coordination note"
                  help="Do not include private contact details here."
                  :validator="communicationNoteValidator">
                  <template #default="{ invalid, describedBy, feedbackClass }">
                    <input
                      id="communication_note"
                      v-model="formValues.communicationNote"
                      name="communication_note"
                      type="text"
                      :class="[
                        'h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                        feedbackClass,
                      ]"
                      placeholder="Async updates are easiest."
                      maxlength="240"
                      :aria-invalid="invalid || undefined"
                      :aria-describedby="describedBy">
                  </template>
                </FormField>
              </div>

              <FormField
                id="applications_closed_note"
                label="Closed applications note"
                help="Optional public note shown only when applications are closed."
                :validator="applicationsClosedNoteValidator">
                <template #default="{ invalid, describedBy, feedbackClass }">
                  <input
                    id="applications_closed_note"
                    v-model="formValues.applicationsClosedNote"
                    name="applications_closed_note"
                    type="text"
                    :class="[
                      'h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                      feedbackClass,
                    ]"
                    placeholder="Applications are paused while we review current interest."
                    maxlength="240"
                    :aria-invalid="invalid || undefined"
                    :aria-describedby="describedBy">
                </template>
              </FormField>

              <FormField
                id="repository_name"
                label="Repository name"
                help="Use up to 100 letters, numbers, dashes, or underscores."
                :validator="repositoryNameValidator">
                <template #default="{ invalid, describedBy, feedbackClass }">
                  <input
                    id="repository_name"
                    v-model="formValues.repositoryName"
                    name="repository_name"
                    type="text"
                    :class="[
                      'h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                      feedbackClass,
                    ]"
                    placeholder="design-review-matchmaker"
                    maxlength="100"
                    :pattern="repositoryNamePattern"
                    autocomplete="off"
                    autocapitalize="none"
                    spellcheck="false"
                    :aria-invalid="invalid || undefined"
                    :aria-describedby="describedBy"
                    :required="isCollaborationStep"
                    @beforeinput="blockInvalidRepositoryNameInput"
                    @input="sanitizeRepositoryName"
                    @paste="pasteRepositoryName">
                </template>
              </FormField>

              <FormField
                id="getting_started_notes"
                label="Private start notes"
                help="Visible only to accepted collaborators and the owner. Markdown is supported."
                :validator="gettingStartedNotesValidator">
                <template #default="{ invalid, describedBy, feedbackClass }">
                  <textarea
                    id="getting_started_notes"
                    v-model="formValues.gettingStartedNotes"
                    name="getting_started_notes"
                    :class="[
                      'min-h-32 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                      feedbackClass,
                    ]"
                    placeholder="Share private setup links, contact details, or first-week context after approval."
                    maxlength="10000"
                    :aria-invalid="invalid || undefined"
                    :aria-describedby="describedBy" />
                </template>
              </FormField>
            </div>

            <aside class="flex h-fit flex-col gap-3 rounded-md border border-border bg-background/35 p-4">
              <div class="flex flex-col gap-1">
                <h2 class="text-sm font-semibold text-foreground">
                  Collaboration readiness
                </h2>
                <p class="text-sm text-muted-foreground">
                  Missing items can stay honest while the idea is still taking shape.
                </p>
              </div>
              <ul class="flex flex-col gap-2">
                <li
                  v-for="item in readinessItems"
                  :key="item.label"
                  class="flex gap-2 text-sm">
                  <Check
                    v-if="item.complete"
                    class="mt-0.5 size-4 shrink-0 text-primary"
                    aria-hidden="true" />
                  <Circle
                    v-else
                    class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                    aria-hidden="true" />
                  <span :class="item.complete ? 'text-foreground' : 'text-muted-foreground'">{{ item.label }}</span>
                </li>
              </ul>
            </aside>
          </section>

          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div
              v-if="idea"
              class="flex items-center gap-2 text-sm">
              <label for="status">Status:</label>
              <FormSelect
                id="status"
                name="status">
                <option
                  value="open"
                  :selected="oldInputString('status', idea.status) === 'open'">
                  Open
                </option>
                <option
                  value="closed"
                  :selected="oldInputString('status', idea.status) === 'closed'">
                  Closed
                </option>
              </FormSelect>
            </div>
            <span v-else />

            <div class="flex flex-wrap justify-end gap-2">
              <Button
                v-if="!idea && currentStep === 'collaboration'"
                type="button"
                variant="outline"
                size="sm"
                @click="returnToBasics">
                Back
              </Button>
              <Button
                v-if="!idea && currentStep === 'basics'"
                type="button"
                size="sm"
                @click="continueToCollaboration">
                Continue
              </Button>
              <Button
                v-else
                type="submit"
                size="sm">
                {{ submitLabel }}
              </Button>
            </div>
          </div>
        </form>
      </CardContent>
    </Card>
  </section>
</template>
