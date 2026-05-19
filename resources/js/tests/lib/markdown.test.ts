import { describe, expect, it } from 'vitest';
import {
  markdownAnchor,
  markdownHeadings,
  stripGeneratedTableOfContents,
  stripMarkdownFormatting,
  tableOfContentsEnd,
  tableOfContentsStart,
  uniqueMarkdownAnchor,
} from '@/lib/markdown';

describe('markdown helpers', () => {
  it('creates stable anchors from headings', () => {
    expect(markdownAnchor(' Build the Thing! ')).toBe('build-the-thing');
    expect(markdownAnchor('***')).toBe('section');
  });

  it('deduplicates repeated anchors', () => {
    const counts = new Map<string, number>();

    expect(uniqueMarkdownAnchor('Roadmap', counts)).toBe('roadmap');
    expect(uniqueMarkdownAnchor('Roadmap', counts)).toBe('roadmap-1');
    expect(uniqueMarkdownAnchor('Roadmap', counts)).toBe('roadmap-2');
  });

  it('extracts readable headings and skips generated table of contents headings', () => {
    expect(markdownHeadings('# Pitch\n\n## **Plan**\n\n## Table of contents\n\n### `Build` phase')).toEqual([
      { level: 1, title: 'Pitch', anchor: 'pitch' },
      { level: 2, title: 'Plan', anchor: 'plan' },
      { level: 3, title: 'Build phase', anchor: 'build-phase' },
    ]);
  });

  it('removes generated table of contents blocks without touching user content', () => {
    const markdown = [
      '# Pitch',
      tableOfContentsStart,
      '- [Pitch](#pitch)',
      tableOfContentsEnd,
      'Real content',
    ].join('\n');

    expect(stripGeneratedTableOfContents(markdown)).toBe('# Pitch\n\nReal content');
    expect(stripGeneratedTableOfContents('## Table of contents\n- [Pitch](#pitch)\n\nReal content')).toBe('Real content');
  });

  it('strips common markdown formatting from labels', () => {
    expect(stripMarkdownFormatting('**Deploy** [`today`](https://example.com)')).toBe('Deploy todayhttps://example.com');
  });
});
