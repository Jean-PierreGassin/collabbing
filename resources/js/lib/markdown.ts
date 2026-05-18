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
