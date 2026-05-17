<script setup lang="ts">
import { computed, ref } from 'vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import FormSelect from '@/components/forms/FormSelect.vue';
import MethodField from '@/components/forms/MethodField.vue';
import PageHeader from '@/components/navigation/PageHeader.vue';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { oldInputString } from '@/lib/forms';
import { markdownHeadings, stripGeneratedTableOfContents, stripMarkdownFormatting, tableOfContentsEnd, tableOfContentsStart, uniqueMarkdownAnchor } from '@/lib/markdown';
import { useSessionStore } from '@/stores/session';
import type { Idea } from '@/types/domain';

const props = defineProps<{
  idea?: Idea;
}>();

const session = useSessionStore();
const contentInput = ref<HTMLTextAreaElement | null>(null);
const repositoryNamePattern = '[A-Za-z0-9_-]+';
const repositoryNameAllowedCharacters = /^[A-Za-z0-9_-]+$/;
const repositoryNameSanitizer = /[^A-Za-z0-9_-]/g;
const markdownFilePattern = /\.(md|markdown)$/i;
const content = ref(stripGeneratedTableOfContents(oldInputString('content', props.idea?.content)));

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

  content.value = stripGeneratedTableOfContents(
    existingContent ? `${existingContent}\n\n${body}` : body
  );
}

function selectMarkdownFiles(event: Event): void {
  const input = event.target as HTMLInputElement;

  void insertMarkdownFiles(Array.from(input.files ?? []));

  input.value = '';
}

function dropMarkdownFiles(event: DragEvent): void {
  void insertMarkdownFiles(Array.from(event.dataTransfer?.files ?? []));
}

const contentBody = computed(() => stripGeneratedTableOfContents(content.value));
const previewHeadings = computed(() => markdownHeadings(contentBody.value));
const minimumPreviewHeadingLevel = computed(() => {
  if (previewHeadings.value.length === 0) {
    return 1;
  }

  return Math.min(...previewHeadings.value.map((heading) => heading.level));
});
const previewHtml = computed(() => renderMarkdownPreview(contentBody.value));
</script>

<template>
  <section class="flex flex-col gap-5">
    <PageHeader
      :title="idea ? 'Edit your idea' : 'Share your idea'"
      description="Describe the problem, the collaboration shape, and the repository plan."
    />

    <Card>
      <CardContent>
        <form :action="idea ? idea.routes.update : session.routes.ideasStore" method="POST" class="flex flex-col gap-5">
          <CsrfField />
          <MethodField v-if="idea" method="PUT" />

          <div class="grid gap-4 md:grid-cols-2">
            <FormField id="title" label="Title">
              <template #default="{ invalid, describedBy }">
                <input id="title" name="title" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" :value="oldInputString('title', idea?.title)" placeholder="A faster way to match design reviewers" maxlength="100" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
              </template>
            </FormField>

            <FormField id="communication" label="Communication">
              <template #default="{ invalid, describedBy }">
                <input id="communication" name="communication" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" :value="oldInputString('communication', idea?.communication)" placeholder="Slack, Discord, email..." maxlength="50" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
              </template>
            </FormField>
          </div>

          <FormField id="repository_name" label="Repository name" help="Use up to 100 letters, numbers, dashes, or underscores.">
            <template #default="{ invalid, describedBy }">
              <input id="repository_name" name="repository_name" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" :value="oldInputString('repository_name', idea?.repositoryName)" placeholder="design-review-matchmaker" maxlength="100" :pattern="repositoryNamePattern" autocomplete="off" autocapitalize="none" spellcheck="false" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required @beforeinput="blockInvalidRepositoryNameInput" @input="sanitizeRepositoryName" @paste="pasteRepositoryName">
            </template>
          </FormField>

          <FormField id="summary" label="Summary" help="Plain text only. This appears on idea cards.">
            <template #default="{ invalid, describedBy }">
              <textarea id="summary" name="summary" class="min-h-24 rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" placeholder="A short plain-text overview of who this helps and why it should exist." maxlength="240" :value="oldInputString('summary', idea?.summary)" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required />
            </template>
          </FormField>

          <FormField id="content" label="Pitch (supports markdown)" help="Drop in markdown files to append a generated table of contents and sectioned notes.">
            <template #default="{ invalid, describedBy }">
              <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-4">
                  <textarea
                    id="content"
                    ref="contentInput"
                    v-model="content"
                    name="content"
                    class="min-h-[28rem] rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40"
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
                        type="file"
                        class="text-sm file:mr-3 file:rounded-md file:border-0 file:bg-primary file:px-3 file:py-2 file:text-sm file:font-medium file:text-primary-foreground hover:file:bg-primary/90"
                        accept=".md,.markdown,text/markdown,text/plain"
                        multiple
                        @change="selectMarkdownFiles"
                      >
                    </div>
                  </div>
                </div>
                <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_16rem]">
                  <section class="flex min-h-[28rem] min-w-0 flex-col gap-3 rounded-md border border-border bg-background/35 p-4" aria-label="Markdown preview">
                    <div class="flex items-center justify-between gap-3 border-b border-border pb-3">
                      <h2 class="text-sm font-semibold text-white">Preview</h2>
                      <span class="text-xs text-muted-foreground">{{ contentBody.length.toLocaleString() }} / 20,000</span>
                    </div>
                    <div class="min-w-0">
                      <MarkdownContent v-if="contentBody.trim()" :html="previewHtml" />
                      <p v-else class="text-sm text-muted-foreground">Start writing to preview the markdown here.</p>
                    </div>
                  </section>
                  <aside class="rounded-md border border-border bg-background/35 p-4 xl:sticky xl:top-24 xl:self-start" aria-label="Preview table of contents">
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground">Contents</h3>
                    <nav v-if="previewHeadings.length > 0" class="flex flex-col gap-1 text-sm">
                      <a
                        v-for="heading in previewHeadings"
                        :key="heading.anchor"
                        :href="`#${heading.anchor}`"
                        class="truncate rounded-sm py-1 text-muted-foreground transition-colors hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
                        :style="{ paddingLeft: `${Math.max(0, heading.level - minimumPreviewHeadingLevel) * 0.75}rem` }"
                      >
                        {{ heading.title }}
                      </a>
                    </nav>
                    <p v-else class="text-sm text-muted-foreground">Headings appear here.</p>
                  </aside>
                </div>
              </div>
            </template>
          </FormField>

          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <label v-if="idea" for="status" class="flex items-center gap-2 text-sm">
              Status:
              <FormSelect id="status" name="status">
                <option value="open" :selected="oldInputString('status', idea.status) === 'open'">Open</option>
                <option value="closed" :selected="oldInputString('status', idea.status) === 'closed'">Closed</option>
              </FormSelect>
            </label>
            <span v-else />

            <Button type="submit" size="sm" :variant="idea ? 'secondary' : 'default'" class="self-start">
              {{ idea ? 'Edit Idea' : 'Share Idea' }}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </section>
</template>
