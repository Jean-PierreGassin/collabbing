<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { ChevronDown, Upload } from '@lucide/vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import FormSelect from '@/components/forms/FormSelect.vue';
import MethodField from '@/components/forms/MethodField.vue';
import PageHeader from '@/components/navigation/PageHeader.vue';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { oldInputString } from '@/lib/forms';
import { maxLengthValidator, repositoryNameValidator } from '@/lib/formValidation';
import { markdownHeadings, stripGeneratedTableOfContents, stripMarkdownFormatting, tableOfContentsEnd, tableOfContentsStart, uniqueMarkdownAnchor } from '@/lib/markdown';
import { useSessionStore } from '@/stores/session';
import type { MarkdownHeading } from '@/lib/markdown';
import type { Idea } from '@/types/domain';

const props = defineProps<{
  idea?: Idea;
}>();

const session = useSessionStore();
const contentInput = ref<HTMLTextAreaElement | null>(null);
const markdownFileInput = ref<HTMLInputElement | null>(null);
const isMobilePreviewTocOpen = ref(false);
const isPreviewContentInView = ref(false);
const activePreviewAnchor = ref('');
const repositoryNamePattern = '[A-Za-z0-9_-]+';
const repositoryNameAllowedCharacters = /^[A-Za-z0-9_-]+$/;
const repositoryNameSanitizer = /[^A-Za-z0-9_-]/g;
const markdownFilePattern = /\.(md|markdown)$/i;
const content = ref(stripGeneratedTableOfContents(oldInputString('content', props.idea?.content)));
const titleValidator = maxLengthValidator(100, 'a title');
const communicationValidator = maxLengthValidator(50, 'a communication preference');
const summaryValidator = maxLengthValidator(240, 'a summary');
const contentValidator = maxLengthValidator(20000, 'a pitch');

let pageTitle = 'Share your idea';
let formAction = session.routes.ideasStore;
let submitVariant: 'default' | 'secondary' = 'default';
let submitLabel = 'Share Idea';

if (props.idea) {
  pageTitle = 'Edit your idea';
  formAction = props.idea.routes.update;
  submitVariant = 'secondary';
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

async function insertMarkdownFiles(files: File[]): Promise<void> {
  const markdownFiles = files.filter(isMarkdownFile);

  if (markdownFiles.length === 0) {
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
const minimumPreviewHeadingLevel = computed(() => {
  if (previewHeadings.value.length === 0) {
    return 1;
  }

  return Math.min(...previewHeadings.value.map((heading) => heading.level));
});
const shouldShowMobilePreviewToc = computed(() => previewHeadings.value.length > 0 && isPreviewContentInView.value);
const activePreviewHeadingIndex = computed(() => previewHeadings.value.findIndex((heading) => heading.anchor === activePreviewAnchor.value));
const activePreviewPathAnchors = computed(() => {
  const path = new Set<string>();
  const index = activePreviewHeadingIndex.value;

  if (index === -1) {
    if (previewHeadings.value[0]) {
      path.add(previewHeadings.value[0].anchor);
    }

    return path;
  }

  const activeLevel = previewHeadings.value[index].level;
  let nextAncestorLevel = activeLevel;

  path.add(previewHeadings.value[index].anchor);

  for (let headingIndex = index - 1; headingIndex >= 0; headingIndex -= 1) {
    const heading = previewHeadings.value[headingIndex];

    if (heading.level < nextAncestorLevel) {
      path.add(heading.anchor);
      nextAncestorLevel = heading.level;
    }
  }

  return path;
});
const visiblePreviewHeadings = computed(() => previewHeadings.value.filter((heading, index) => {
  if (heading.level === minimumPreviewHeadingLevel.value) {
    return true;
  }

  const parentAnchor = parentPreviewHeadingAnchor(index);

  return parentAnchor !== null && activePreviewPathAnchors.value.has(parentAnchor);
}));
const previewHtml = computed(() => renderMarkdownPreview(contentBody.value));

let previewHeadingObserver: IntersectionObserver | null = null;
let previewContentObserver: IntersectionObserver | null = null;
let observePreviewTimer: number | null = null;
let previewNavigationTimer: number | null = null;
let isPreviewNavigationLocked = false;

function parentPreviewHeadingAnchor(index: number): string | null {
  const heading = previewHeadings.value[index];

  for (let headingIndex = index - 1; headingIndex >= 0; headingIndex -= 1) {
    if (previewHeadings.value[headingIndex].level < heading.level) {
      return previewHeadings.value[headingIndex].anchor;
    }
  }

  return null;
}

function previewHeadingDepth(heading: MarkdownHeading): number {
  return Math.max(0, heading.level - minimumPreviewHeadingLevel.value);
}

function isActivePreviewHeading(heading: MarkdownHeading): boolean {
  return activePreviewAnchor.value === heading.anchor;
}

function mobilePreviewTocChevronClass(): string | undefined {
  if (isMobilePreviewTocOpen.value) {
    return 'rotate-180';
  }

  return undefined;
}

function previewHeadingLinkClass(heading: MarkdownHeading): string {
  if (isActivePreviewHeading(heading)) {
    return 'border-primary text-primary';
  }

  return 'border-transparent text-muted-foreground hover:border-primary/50 hover:text-white';
}

function previewHeadingTextClass(heading: MarkdownHeading): string | undefined {
  if (previewHeadingDepth(heading) > 0) {
    return 'text-[0.8125rem]';
  }

  return undefined;
}

function resetPreviewHeadingObserver(): void {
  if (observePreviewTimer !== null) {
    window.clearTimeout(observePreviewTimer);
    observePreviewTimer = null;
  }

  previewHeadingObserver?.disconnect();
  previewHeadingObserver = null;
}

function resetPreviewContentObserver(): void {
  previewContentObserver?.disconnect();
  previewContentObserver = null;
  window.removeEventListener('scroll', updatePreviewContentVisibility);
  window.removeEventListener('resize', updatePreviewContentVisibility);
  isPreviewContentInView.value = false;
}

function resetPreviewNavigationLock(): void {
  if (previewNavigationTimer !== null) {
    window.clearTimeout(previewNavigationTimer);
    previewNavigationTimer = null;
  }

  isPreviewNavigationLocked = false;
}

function lockPreviewNavigation(anchor: string): void {
  if (previewNavigationTimer !== null) {
    window.clearTimeout(previewNavigationTimer);
  }

  isPreviewNavigationLocked = true;
  activePreviewAnchor.value = anchor;
  previewNavigationTimer = window.setTimeout(() => {
    activePreviewAnchor.value = anchor;
    isPreviewNavigationLocked = false;
    previewNavigationTimer = null;
  }, 900);
}

function observePreviewHeadings(): void {
  resetPreviewHeadingObserver();

  if (previewHeadings.value.length === 0) {
    return;
  }

  const headingElements = previewHeadings.value
    .map((heading) => document.getElementById(heading.anchor))
    .filter((element): element is HTMLElement => element !== null);

  if (headingElements.length === 0) {
    return;
  }

  activePreviewAnchor.value ||= headingElements[0].id;
  previewHeadingObserver = new IntersectionObserver((entries) => {
    if (isPreviewNavigationLocked) {
      return;
    }

    const visibleEntry = entries
      .filter((entry) => entry.isIntersecting)
      .sort((left, right) => left.boundingClientRect.top - right.boundingClientRect.top)[0];

    if (visibleEntry?.target.id) {
      activePreviewAnchor.value = visibleEntry.target.id;
    }
  }, {
    rootMargin: '-18% 0px -65% 0px',
    threshold: [0, 1],
  });

  headingElements.forEach((element) => previewHeadingObserver?.observe(element));
}

function updatePreviewContentVisibility(): void {
  if (previewHeadings.value.length === 0) {
    isPreviewContentInView.value = false;

    return;
  }

  const previewElement = document.getElementById(previewDescriptionId);

  if (!previewElement) {
    isPreviewContentInView.value = false;

    return;
  }

  const previewBounds = previewElement.getBoundingClientRect();
  const readingOffset = 96;

  isPreviewContentInView.value = previewBounds.top <= readingOffset && previewBounds.bottom > readingOffset;
}

function observePreviewContent(): void {
  resetPreviewContentObserver();

  if (previewHeadings.value.length === 0) {
    return;
  }

  const previewElement = document.getElementById(previewDescriptionId);

  if (!previewElement) {
    return;
  }

  previewContentObserver = new IntersectionObserver((entries) => {
    if (!entries.some((entry) => entry.isIntersecting)) {
      isPreviewContentInView.value = false;

      return;
    }

    updatePreviewContentVisibility();
  }, {
    rootMargin: '-64px 0px -22% 0px',
    threshold: [0, 0.01],
  });

  previewContentObserver.observe(previewElement);
  window.addEventListener('scroll', updatePreviewContentVisibility, { passive: true });
  window.addEventListener('resize', updatePreviewContentVisibility);
  updatePreviewContentVisibility();
}

async function openPreviewHeading(anchor: string): Promise<void> {
  isMobilePreviewTocOpen.value = true;
  lockPreviewNavigation(anchor);

  await nextTick();

  window.setTimeout(() => requestAnimationFrame(() => {
    const heading = document.getElementById(anchor);

    if (heading) {
      const mobileContents = document.getElementById('mobile-preview-contents');
      let mobileOffset = 96;

      if (window.matchMedia('(max-width: 1699px)').matches) {
        mobileOffset = (mobileContents?.getBoundingClientRect().height ?? 0) + 16;
      }

      window.scrollTo({
        top: heading.getBoundingClientRect().top + window.scrollY - mobileOffset,
        behavior: 'smooth',
      });
    }

    window.history.replaceState(null, '', `#${anchor}`);
  }), 0);
}

watch(previewHeadings, async (nextHeadings) => {
  if (nextHeadings.length === 0) {
    activePreviewAnchor.value = '';
    isMobilePreviewTocOpen.value = false;
    resetPreviewHeadingObserver();
    resetPreviewContentObserver();
    resetPreviewNavigationLock();

    return;
  }

  if (!nextHeadings.some((heading) => heading.anchor === activePreviewAnchor.value)) {
    activePreviewAnchor.value = nextHeadings[0]?.anchor ?? '';
  }

  await nextTick();
  resetPreviewHeadingObserver();
  resetPreviewContentObserver();
  observePreviewTimer = window.setTimeout(() => {
    observePreviewHeadings();
    observePreviewContent();
  }, 280);
}, { immediate: true });

onBeforeUnmount(() => {
  resetPreviewHeadingObserver();
  resetPreviewContentObserver();
  resetPreviewNavigationLock();
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

            <FormField id="communication" label="Communication" :validator="communicationValidator">
              <template #default="{ invalid, describedBy, feedbackClass }">
                <input id="communication" name="communication" type="text" :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]" :defaultValue="oldInputString('communication', idea?.communication)" placeholder="Slack, Discord, email..." maxlength="50" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
              </template>
            </FormField>
          </div>

          <FormField id="repository_name" label="Repository name" help="Use up to 100 letters, numbers, dashes, or underscores." :validator="repositoryNameValidator">
            <template #default="{ invalid, describedBy, feedbackClass }">
              <input id="repository_name" name="repository_name" type="text" :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]" :defaultValue="oldInputString('repository_name', idea?.repositoryName)" placeholder="design-review-matchmaker" maxlength="100" :pattern="repositoryNamePattern" autocomplete="off" autocapitalize="none" spellcheck="false" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required @beforeinput="blockInvalidRepositoryNameInput" @input="sanitizeRepositoryName" @paste="pasteRepositoryName">
            </template>
          </FormField>

          <FormField id="summary" label="Summary" help="Plain text only. This appears on idea cards." :validator="summaryValidator">
            <template #default="{ invalid, describedBy, feedbackClass }">
              <textarea id="summary" name="summary" :class="['min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]" placeholder="A short plain-text overview of who this helps and why it should exist." maxlength="240" :defaultValue="oldInputString('summary', idea?.summary)" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required />
            </template>
          </FormField>

          <FormField id="content" label="Pitch (supports markdown)" help="Drop in markdown files to append a generated table of contents and sectioned notes." :validator="contentValidator">
            <template #default="{ invalid, describedBy, feedbackClass }">
              <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-4">
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
                    <span class="font-medium text-white">Import markdown files</span>
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
                  </div>
                </div>
                <div class="relative flex min-w-0 flex-col gap-3">
                  <Teleport to="body">
                    <Transition name="toc-float">
                      <div
                        v-if="shouldShowMobilePreviewToc"
                        id="mobile-preview-contents"
                        class="fixed inset-x-4 top-3 z-[70] rounded-2xl border border-border bg-background/90 px-3 py-2 shadow-lg shadow-background/35 backdrop-blur min-[1700px]:hidden"
                      >
                        <div class="mx-auto flex max-w-2xl flex-col">
                          <button
                            type="button"
                            class="flex w-full items-center justify-between gap-3 rounded-full border-l-2 border-primary/70 py-1.5 pl-3 pr-1 text-left text-xs font-semibold uppercase text-white transition-colors hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
                            :aria-expanded="isMobilePreviewTocOpen"
                            @click="isMobilePreviewTocOpen = !isMobilePreviewTocOpen"
                          >
                            Contents
                            <ChevronDown :class="['size-4 text-primary transition-transform duration-200', mobilePreviewTocChevronClass()]" aria-hidden="true" />
                          </button>

                          <Transition name="toc-mobile">
                            <nav v-if="isMobilePreviewTocOpen" aria-label="Preview table of contents" class="scrollbar-hidden mt-2 max-h-[min(18rem,55dvh)] overflow-y-auto overscroll-contain border-l border-border py-2 text-sm">
                              <TransitionGroup name="toc-item" tag="div" class="flex flex-col gap-1">
                                <a
                                  v-for="heading in visiblePreviewHeadings"
                                  :key="heading.anchor"
                                  :href="`#${heading.anchor}`"
                                  :style="{ paddingLeft: `${0.75 + previewHeadingDepth(heading) * 0.9}rem` }"
                                  :class="[
                                    '-ml-px flex min-h-8 items-center border-l-2 py-1.5 pr-2 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50',
                                    previewHeadingLinkClass(heading),
                                  ]"
                                  @click.prevent="openPreviewHeading(heading.anchor)"
                                >
                                  <span class="truncate" :class="previewHeadingTextClass(heading)">{{ heading.title }}</span>
                                </a>
                              </TransitionGroup>
                            </nav>
                          </Transition>
                        </div>
                      </div>
                    </Transition>
                  </Teleport>

                  <Transition name="toc-float">
                    <aside
                      v-if="previewHeadings.length > 0"
                      class="hidden min-[1700px]:absolute min-[1700px]:inset-y-0 min-[1700px]:right-full min-[1700px]:mr-4 min-[1700px]:block min-[1700px]:w-56"
                    >
                      <div class="sticky top-24 p-2">
                        <h2 class="pb-2 pl-3 text-sm font-semibold text-white">Contents</h2>
                        <nav aria-label="Preview table of contents" class="scrollbar-hidden flex max-h-[calc(100svh-7rem)] flex-col gap-1 overflow-y-auto overscroll-contain border-l border-border text-sm">
                          <TransitionGroup name="toc-item" tag="div" class="flex flex-col gap-1">
                            <a
                              v-for="heading in visiblePreviewHeadings"
                              :key="heading.anchor"
                              :href="`#${heading.anchor}`"
                              :style="{ paddingLeft: `${0.75 + previewHeadingDepth(heading) * 0.9}rem` }"
                              :class="[
                                '-ml-px flex min-h-8 items-center border-l-2 py-1.5 pr-2 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50',
                                previewHeadingLinkClass(heading),
                              ]"
                              @click.prevent="openPreviewHeading(heading.anchor)"
                            >
                              <span class="truncate" :class="previewHeadingTextClass(heading)">{{ heading.title }}</span>
                            </a>
                          </TransitionGroup>
                        </nav>
                      </div>
                    </aside>
                  </Transition>

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

            <Button type="submit" size="sm" :variant="submitVariant" class="self-end">
              {{ submitLabel }}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </section>
</template>
