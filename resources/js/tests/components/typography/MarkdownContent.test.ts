import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import { describe, expect, it } from 'vitest';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';

describe('MarkdownContent', () => {
  it('isolates external links from the opening window', async () => {
    const wrapper = mount(MarkdownContent, {
      props: {
        html: '<a href="https://example.com">External</a><a href="/ideas">Internal</a>',
      },
    });

    await nextTick();
    await nextTick();

    const links = wrapper.findAll('a');

    expect(links[0].attributes('target')).toBe('_blank');
    expect(links[0].attributes('rel')).toBe('noopener noreferrer');
    expect(links[1].attributes('target')).toBeUndefined();
    expect(links[1].attributes('rel')).toBeUndefined();
  });

  it('rechecks links when rendered markdown changes', async () => {
    const wrapper = mount(MarkdownContent, {
      props: {
        html: '<p>Draft</p>',
      },
    });

    await wrapper.setProps({
      html: '<a href="https://example.com/docs">Docs</a>',
    });
    await nextTick();
    await nextTick();

    const link = wrapper.get('a');

    expect(link.attributes('target')).toBe('_blank');
    expect(link.attributes('rel')).toBe('noopener noreferrer');
  });
});
