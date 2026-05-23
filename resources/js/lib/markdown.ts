export interface MarkdownHeading {
  level: number;
  title: string;
  anchor: string;
}

export const tableOfContentsStart = '<!-- collabbing:toc:start -->';
export const tableOfContentsEnd = '<!-- collabbing:toc:end -->';

export function markdownAnchor(title: string): string {
  return title
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .trim()
    .replace(/\s+/g, '-') || 'section';
}

export function uniqueMarkdownAnchor(title: string, counts: Map<string, number>): string {
  const anchor = markdownAnchor(title);
  const count = counts.get(anchor) ?? 0;

  counts.set(anchor, count + 1);

  if (count === 0) {
    return anchor;
  }

  return `${anchor}-${count}`;
}

export function stripMarkdownFormatting(value: string): string {
  return value
    .replace(/[`*_~[\]()]/g, '')
    .replace(/\s+/g, ' ')
    .trim();
}

export function stripGeneratedTableOfContents(markdown: string): string {
  const start = markdown.indexOf(tableOfContentsStart);
  const end = markdown.indexOf(tableOfContentsEnd);

  if (start !== -1 && end !== -1 && end > start) {
    return `${markdown.slice(0, start)}${markdown.slice(end + tableOfContentsEnd.length)}`.trim();
  }

  return markdown.replace(/^## Table of contents\n(?:[ \t]*- .+\n)+\n*/i, '').trim();
}

export function markdownHeadings(markdown: string): MarkdownHeading[] {
  const counts = new Map<string, number>();

  return markdown
    .split('\n')
    .map((line) => line.match(/^(#{1,6})\s+(.+?)\s*#*\s*$/))
    .filter((match): match is RegExpMatchArray => Boolean(match))
    .map((match) => ({
      level: match[1].length,
      title: stripMarkdownFormatting(match[2]),
      anchor: uniqueMarkdownAnchor(match[2], counts),
    }))
    .filter((heading) => heading.title.toLowerCase() !== 'table of contents');
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

export function renderMarkdownPreview(markdown: string): string {
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
    if ([
      tableOfContentsStart,
      tableOfContentsEnd,
    ].includes(line.trim())) {
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

      isCodeBlock = !isCodeBlock;

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
