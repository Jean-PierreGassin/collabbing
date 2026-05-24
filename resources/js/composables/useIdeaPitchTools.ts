import { computed, nextTick, ref, type Component, type Ref } from 'vue';
import { Handshake, Lightbulb, Rocket, Users } from '@lucide/vue';
import { markdownHeadings, renderMarkdownPreview, stripGeneratedTableOfContents } from '@/lib/markdown';

type FeedbackTone = 'muted' | 'success' | 'warning';

type WritingSection = {
  description: string;
  icon: Component;
  label: string;
  markdown: string;
};

const markdownFilePattern = /\.(md|markdown)$/i;

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

function markdownTitle(file: File): string {
  return file.name
    .replace(markdownFilePattern, '')
    .replace(/[-_]+/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
    .replace(/\b\w/g, (letter) => letter.toUpperCase()) || 'Imported notes';
}

function isMarkdownFile(file: File): boolean {
  return markdownFilePattern.test(file.name) || [
    'text/markdown',
    'text/plain',
  ].includes(file.type);
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

export function useIdeaPitchTools(
  content: Ref<string>,
  contentInput: Ref<HTMLTextAreaElement | null>,
  markdownFileInput: Ref<HTMLInputElement | null>,
) {
  const markdownImportFeedback = ref('');
  const markdownImportFeedbackTone = ref<FeedbackTone>('muted');
  const contentBody = computed(() => stripGeneratedTableOfContents(content.value));
  const previewHeadings = computed(() => markdownHeadings(contentBody.value));
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

  return {
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
  };
}
