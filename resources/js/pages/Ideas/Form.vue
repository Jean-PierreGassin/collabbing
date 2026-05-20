<script setup lang="ts">
import { computed, nextTick, ref, type Component } from 'vue';
import { Handshake, Lightbulb, Rocket, Upload, Users } from '@lucide/vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import FormSelect from '@/components/forms/FormSelect.vue';
import MethodField from '@/components/forms/MethodField.vue';
import PageHeader from '@/components/navigation/PageHeader.vue';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import MarkdownTableOfContents from '@/components/typography/MarkdownTableOfContents.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { oldInputString } from '@/lib/forms';
import { maxLengthValidator, repositoryNameValidator } from '@/lib/formValidation';
import { markdownHeadings, stripGeneratedTableOfContents, stripMarkdownFormatting, tableOfContentsEnd, tableOfContentsStart, uniqueMarkdownAnchor } from '@/lib/markdown';
import { useSessionStore } from '@/stores/session';
import type { Idea } from '@/types/domain';

const props = defineProps<{
  idea?: Idea;
}>();

const session = useSessionStore();
const contentInput = ref<HTMLTextAreaElement | null>(null);
const markdownFileInput = ref<HTMLInputElement | null>(null);
const markdownImportFeedback = ref('');
const markdownImportFeedbackTone = ref<'muted' | 'success' | 'warning'>('muted');
const repositoryNamePattern = '[A-Za-z0-9_-]+';
const repositoryNameAllowedCharacters = /^[A-Za-z0-9_-]+$/;
const repositoryNameSanitizer = /[^A-Za-z0-9_-]/g;
const markdownFilePattern = /\.(md|markdown)$/i;
const content = ref(stripGeneratedTableOfContents(oldInputString('content', props.idea?.content)));
const titleValidator = maxLengthValidator(100, 'a title');
const taglineValidator = maxLengthValidator(140, 'a tagline');
const communicationValidator = maxLengthValidator(50, 'a communication preference');
const summaryValidator = maxLengthValidator(240, 'a summary');
const tagsValidator = maxLengthValidator(240, 'tags');
const contentValidator = maxLengthValidator(20000, 'a pitch');
const tagsText = computed(() => props.idea?.tags.join(', ') ?? '');

type WritingSection = {
  description: string;
  icon: Component;
  label: string;
  markdown: string;
};

const writingSections: WritingSection[] = [
  {
    description: 'Add the user pain and why current workarounds fall short.',
    icon: Lightbulb,
    label: 'Problem',
    markdown: '## Problem\n\nWho feels this pain today, and what makes the current workaround frustrating?',
  },
  {
    description: 'Add the first people this idea should help.',
    icon: Users,
    label: 'Audience',
    markdown: '## Audience\n\nWho should care about this first, and what context do they bring?',
  },
  {
    description: 'Add the smallest useful version of the idea.',
    icon: Rocket,
    label: 'First version',
    markdown: '## First version\n\n- What is the smallest useful workflow?\n- What can wait until later?',
  },
  {
    description: 'Add the help needed from collaborators.',
    icon: Handshake,
    label: 'Collaboration',
    markdown: '## Collaboration\n\nWhat kind of help would move this forward, and how should collaborators join in?',
  },
];

let pageTitle = 'Share your idea';
let formAction = session.routes.ideasStore;
let submitLabel = 'Share Idea';

if (props.idea) {
  pageTitle = 'Edit your idea';
  formAction = props.idea.routes.update;
  submitLabel = 'Edit Idea';
}

function sanitizeRepositoryName(event: Event): void {
  const input = event.target as HTMLInputElement;
  const sanitized = input.value.replace(repositoryNameSanitizer, '');

  if (input.value !== sanitized) {
    input.value = sanitized;
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

function markdownTitle(file: File): string {
  return file.name
    .replace(markdownFilePattern, '')
    .replace(/[-_]+/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
    .replace(/\b\w/g, (letter) => letter.toUpperCase()) || 'Imported notes';
}

function escapeHtml(value: string): string {
  return value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function inlineMarkdownToHtml(value: string): string {
  let html = escapeHtml(value);

  html = html.replace(/`([^`]+)`/g, '<code>$1</code>');
  html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
  html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');
  html = html.replace(/\[([^\]]+)\]\((https?:\/\/[^)\s]+)\)/g, '<a href="$2">$1</a>');
  html = html.replace(/\[([^\]]+)\]\(#([^)]+)\)/g, '<a href="#$2">$1</a>');

  return html;
}

function renderMarkdownPreview(markdown: string): string {
  const lines = markdown.split('\n');
  const html: string[] = [];
  let paragraph: string[] = [];
  let listItems: string[] = [];
  let blockquote: string[] = [];
  let codeLines: string[] = [];
  let isCodeBlock = false;
  const headingCounts = new Map<string, number>();

  const flushParagraph = (): void => {
    if (paragraph.length === 0) {
      return;
    }

    html.push(`<p>${inlineMarkdownToHtml(paragraph.join(' '))}</p>`);
    paragraph = [];
  };

  const flushList = (): void => {
    if (listItems.length === 0) {
      return;
    }

    html.push(`<ul>${listItems.map((item) => `<li>${inlineMarkdownToHtml(item)}</li>`).join('')}</ul>`);
    listItems = [];
  };

  const flushBlockquote = (): void => {
    if (blockquote.length === 0) {
      return;
    }

    html.push(`<blockquote>${blockquote.map((line) => `<p>${inlineMarkdownToHtml(line)}</p>`).join('')}</blockquote>`);
    blockquote = [];
  };

  lines.forEach((line) => {
    if ([tableOfContentsStart, tableOfContentsEnd].includes(line.trim())) {
      return;
    }

    if (line.trim().startsWith('```')) {
      flushParagraph();
      flushList();
      flushBlockquote();

      if (isCodeBlock) {
        html.push(`<pre><code>${escapeHtml(codeLines.join('\n'))}</code></pre>`);
        codeLines = [];
      }

      isCodeBlock = ! isCodeBlock;

      return;
    }

    if (isCodeBlock) {
      codeLines.push(line);

      return;
    }

    const heading = line.match(/^(#{1,6})\s+(.+?)\s*#*\s*$/);
    const listItem = line.match(/^\s*[-*]\s+(.+)$/);
    const quote = line.match(/^>\s?(.+)$/);

    if (heading) {
      flushParagraph();
      flushList();
      flushBlockquote();

      html.push(`<h${heading[1].length} id="${uniqueMarkdownAnchor(heading[2], headingCounts)}">${inlineMarkdownToHtml(stripMarkdownFormatting(heading[2]))}</h${heading[1].length}>`);

      return;
    }

    if (listItem) {
      flushParagraph();
      flushBlockquote();
      listItems.push(listItem[1]);

      return;
    }

    if (quote) {
      flushParagraph();
      flushList();
      blockquote.push(quote[1]);

      return;
    }

    if (line.trim() === '') {
      flushParagraph();
      flushList();
      flushBlockquote();

      return;
    }

    flushList();
    flushBlockquote();
    paragraph.push(line.trim());
  });

  flushParagraph();
  flushList();
  flushBlockquote();

  if (codeLines.length > 0) {
    html.push(`<pre><code>${escapeHtml(codeLines.join('\n'))}</code></pre>`);
  }

  return html.join('\n');
}

function isMarkdownFile(file: File): boolean {
  return markdownFilePattern.test(file.name) || ['text/markdown', 'text/plain'].includes(file.type);
}

function insertionBoundaryBefore(value: string): string {
  if (!value.trim()) {
    return '';
  }

  if (value.endsWith('\n\n')) {
    return '';
  }

  if (value.endsWith('\n')) {
    return '\n';
  }

  return '\n\n';
}

function insertionBoundaryAfter(value: string): string {
  if (!value.trim()) {
    return '';
  }

  if (value.startsWith('\n\n')) {
    return '';
  }

  if (value.startsWith('\n')) {
    return '\n';
  }

  return '\n\n';
}

function focusContentAt(position: number): void {
  void nextTick(() => {
    const textarea = contentInput.value;

    if (!textarea) {
      return;
    }

    textarea.focus();
    textarea.setSelectionRange(position, position);
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
  });
}

function insertWritingSection(section: WritingSection): void {
  const textarea = contentInput.value;
  const currentContent = content.value;
  const selectionStart = textarea?.selectionStart ?? currentContent.length;
  const selectionEnd = textarea?.selectionEnd ?? currentContent.length;
  const before = currentContent.slice(0, selectionStart);
  const after = currentContent.slice(selectionEnd);
  const prefix = insertionBoundaryBefore(before);
  const suffix = insertionBoundaryAfter(after);
  const insertion = section.markdown.trim();
  const cursorPosition = before.length + prefix.length + insertion.length;

  content.value = `${before}${prefix}${insertion}${suffix}${after}`;
  focusContentAt(cursorPosition);
}

function setMarkdownImportFeedback(message: string, tone: 'success' | 'warning'): void {
  markdownImportFeedback.value = message;
  markdownImportFeedbackTone.value = tone;
}

async function insertMarkdownFiles(files: File[]): Promise<void> {
  if (files.length === 0) {
    return;
  }

  const markdownFiles = files.filter(isMarkdownFile);
  const skippedCount = files.length - markdownFiles.length;

  if (markdownFiles.length === 0) {
    setMarkdownImportFeedback('No markdown files were imported. Choose .md or .markdown files.', 'warning');

    return;
  }

  const sections = await Promise.all(markdownFiles.map(async (file) => ({
    title: markdownTitle(file),
    content: (await file.text()).trim(),
  })));

  const body = sections
    .map((section) => `## ${section.title}\n\n${section.content}`)
    .join('\n\n');

  const existingContent = contentBody.value.trim();
  let nextContent = body;

  if (existingContent) {
    nextContent = `${existingContent}\n\n${body}`;
  }

  content.value = stripGeneratedTableOfContents(nextContent);

  let feedback = `Imported ${markdownFiles.length.toLocaleString()} markdown ${markdownFiles.length === 1 ? 'file' : 'files'}.`;

  if (skippedCount > 0) {
    feedback = `${feedback} Skipped ${skippedCount.toLocaleString()} unsupported ${skippedCount === 1 ? 'file' : 'files'}.`;
  }

  setMarkdownImportFeedback(feedback, 'success');
}

function selectMarkdownFiles(event: Event): void {
  const input = event.target as HTMLInputElement;

  void insertMarkdownFiles(Array.from(input.files ?? []));

  input.value = '';
}

function openMarkdownFilePicker(): void {
  markdownFileInput.value?.click();
}

function dropMarkdownFiles(event: DragEvent): void {
  void insertMarkdownFiles(Array.from(event.dataTransfer?.files ?? []));
}

const contentBody = computed(() => stripGeneratedTableOfContents(content.value));
const previewHeadings = computed(() => markdownHeadings(contentBody.value));
const previewDescriptionId = 'idea-form-preview-description';
const previewHtml = computed(() => renderMarkdownPreview(contentBody.value));
const markdownImportFeedbackClass = computed(() => {
  if (markdownImportFeedbackTone.value === 'success') {
    return 'text-emerald-300';
  }

  if (markdownImportFeedbackTone.value === 'warning') {
    return 'text-amber-300';
  }

  return 'text-muted-foreground';
});
</script>

<template>
  <section class="flex flex-col gap-5">
    <PageHeader
      :title="pageTitle"
      description="Describe the problem, the collaboration shape, and the repository plan."
    />

    <Card class="overflow-visible">
      <CardContent>
        <form :action="formAction" method="POST" class="flex flex-col gap-5">
          <CsrfField />
          <MethodField v-if="idea" method="PUT" />

          <div class="grid gap-4 md:grid-cols-2">
            <FormField id="title" label="Title" :validator="titleValidator">
              <template #default="{ invalid, describedBy, feedbackClass }">
                <input id="title" name="title" type="text" :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]" :defaultValue="oldInputString('title', idea?.title)" placeholder="A faster way to match design reviewers" maxlength="100" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
              </template>
            </FormField>

            <FormField id="tagline" label="Tagline" help="Short card copy for scanning the ideas list." :validator="taglineValidator">
              <template #default="{ invalid, describedBy, feedbackClass }">
                <input id="tagline" name="tagline" type="text" :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]" :defaultValue="oldInputString('tagline', idea?.tagline)" placeholder="Match reviewers with work that needs focused feedback" maxlength="140" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
              </template>
            </FormField>

            <FormField id="communication" label="Communication" :validator="communicationValidator">
              <template #default="{ invalid, describedBy, feedbackClass }">
                <input id="communication" name="communication" type="text" :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]" :defaultValue="oldInputString('communication', idea?.communication)" placeholder="Slack, Discord, email..." maxlength="50" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
              </template>
            </FormField>

            <FormField id="tags" label="Tags" help="Separate tags with commas." :validator="tagsValidator">
              <template #default="{ invalid, describedBy, feedbackClass }">
                <input id="tags" name="tags" type="text" :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]" :defaultValue="oldInputString('tags', tagsText)" placeholder="design, review, workflow" maxlength="240" autocomplete="off" :aria-invalid="invalid || undefined" :aria-describedby="describedBy">
              </template>
            </FormField>
          </div>

          <FormField id="repository_name" label="Repository name" help="Use up to 100 letters, numbers, dashes, or underscores." :validator="repositoryNameValidator">
            <template #default="{ invalid, describedBy, feedbackClass }">
              <input id="repository_name" name="repository_name" type="text" :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]" :defaultValue="oldInputString('repository_name', idea?.repositoryName)" placeholder="design-review-matchmaker" maxlength="100" :pattern="repositoryNamePattern" autocomplete="off" autocapitalize="none" spellcheck="false" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required @beforeinput="blockInvalidRepositoryNameInput" @input="sanitizeRepositoryName" @paste="pasteRepositoryName">
            </template>
          </FormField>

          <FormField id="summary" label="Summary" help="Plain text only. This appears on the idea page." :validator="summaryValidator">
            <template #default="{ invalid, describedBy, feedbackClass }">
              <textarea id="summary" name="summary" :class="['min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]" placeholder="A short plain-text overview of who this helps and why it should exist." maxlength="240" :defaultValue="oldInputString('summary', idea?.summary)" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required />
            </template>
          </FormField>

          <FormField id="content" label="Pitch (supports markdown)" help="Drop in markdown files to append a generated table of contents and sectioned notes." :validator="contentValidator">
            <template #default="{ invalid, describedBy, feedbackClass }">
              <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-3">
                  <div class="flex flex-wrap gap-2" role="toolbar" aria-label="Pitch section inserts">
                    <div v-for="section in writingSections" :key="section.label" class="group relative">
                      <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        class="size-9"
                        :aria-label="`Insert ${section.label} section`"
                        @mousedown.prevent
                        @click="insertWritingSection(section)"
                      >
                        <component :is="section.icon" class="size-4 text-primary" aria-hidden="true" />
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
                    :class="['min-h-[28rem] w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]"
                    placeholder="Explain the problem, who it helps, and what a first version should do."
                    maxlength="20000"
                    :aria-invalid="invalid || undefined"
                    :aria-describedby="describedBy"
                    required
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
                        <Upload class="size-4 text-primary" aria-hidden="true" />
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

                  <section :id="previewDescriptionId" class="flex min-h-[28rem] min-w-0 flex-col gap-3 rounded-md border border-border bg-background/35 p-4" aria-label="Markdown preview">
                    <div class="flex items-center justify-between gap-3 border-b border-border pb-3">
                      <h2 class="text-sm font-semibold text-white">Preview</h2>
                      <span class="text-xs text-muted-foreground">{{ contentBody.length.toLocaleString() }} / 20,000</span>
                    </div>
                    <div class="min-w-0">
                      <MarkdownContent v-if="contentBody.trim()" :html="previewHtml" />
                      <p v-else class="text-sm text-muted-foreground">Start writing to preview the markdown here.</p>
                    </div>
                  </section>
                </div>
              </div>
            </template>
          </FormField>

          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div v-if="idea" class="flex items-center gap-2 text-sm">
              <label for="status">Status:</label>
              <FormSelect id="status" name="status">
                <option value="open" :selected="oldInputString('status', idea.status) === 'open'">Open</option>
                <option value="closed" :selected="oldInputString('status', idea.status) === 'closed'">Closed</option>
              </FormSelect>
            </div>
            <span v-else />

            <Button type="submit" size="sm" class="self-end">
              {{ submitLabel }}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </section>
</template>
