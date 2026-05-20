import { flushPromises, mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import IdeaForm from '@/pages/Ideas/Form.vue';

const session = vi.hoisted(() => ({
  csrfToken: 'test-token',
  errors: {} as Record<string, string>,
  oldInput: {} as Record<string, string>,
  routes: {
    ideasStore: '/ideas',
  },
}));

vi.mock('@/stores/session', () => ({
  useSessionStore: () => session,
}));

function mountForm() {
  return mount(IdeaForm, {
    attachTo: document.body,
    global: {
      stubs: {
        MarkdownContent: {
          props: ['html'],
          template: '<div class="markdown-content" v-html="html" />',
        },
        MarkdownTableOfContents: {
          template: '<nav aria-label="Preview table of contents" />',
        },
      },
    },
  });
}

function markdownFile(name: string, content: string, type = 'text/markdown'): File {
  const file = new File([], name, { type });

  Object.defineProperty(file, 'text', {
    value: vi.fn().mockResolvedValue(content),
  });

  return file;
}

describe('Ideas/Form', () => {
  beforeEach(() => {
    session.errors = {};
    session.oldInput = {};
  });

  it('inserts pitch sections at the cursor without leaving the textarea', async () => {
    const wrapper = mountForm();
    const textarea = wrapper.get<HTMLTextAreaElement>('textarea#content');

    await textarea.setValue('Existing pitch.');

    textarea.element.focus();
    textarea.element.setSelectionRange(textarea.element.value.length, textarea.element.value.length);

    await wrapper.get('button[aria-label="Insert First version section"]').trigger('click');
    await nextTick();

    const section = '## First version\n\n- What is the smallest useful workflow?\n- What can wait until later?';
    const expectedContent = `Existing pitch.\n\n${section}`;

    expect(textarea.element.value).toBe(expectedContent);
    expect(document.activeElement).toBe(textarea.element);
    expect(textarea.element.selectionStart).toBe(expectedContent.length);
    expect(textarea.element.selectionEnd).toBe(expectedContent.length);
  });

  it('replaces selected text with a pitch section', async () => {
    const wrapper = mountForm();
    const textarea = wrapper.get<HTMLTextAreaElement>('textarea#content');

    await textarea.setValue('Problem notes need structure.');

    textarea.element.focus();
    textarea.element.setSelectionRange(0, 'Problem notes'.length);

    await wrapper.get('button[aria-label="Insert Problem section"]').trigger('click');
    await nextTick();

    expect(textarea.element.value).toContain('## Problem\n\nWho feels this pain today');
    expect(textarea.element.value).toContain('\n\n need structure.');
    expect(document.activeElement).toBe(textarea.element);
  });

  it('announces markdown imports politely', async () => {
    const wrapper = mountForm();
    const input = wrapper.get<HTMLInputElement>('input#content_markdown_files');
    const file = markdownFile('launch-plan.md', '# Imported notes');

    Object.defineProperty(input.element, 'files', {
      configurable: true,
      value: [file],
    });

    await input.trigger('change');
    await flushPromises();

    expect(wrapper.get('[role="status"]').text()).toBe('Imported 1 markdown file.');
    expect(wrapper.get('[role="status"]').attributes('aria-live')).toBe('polite');
    expect(wrapper.get<HTMLTextAreaElement>('textarea#content').element.value).toContain('## Launch Plan\n\n# Imported notes');
  });

  it('announces unsupported markdown imports without changing the pitch', async () => {
    const wrapper = mountForm();
    const textarea = wrapper.get<HTMLTextAreaElement>('textarea#content');
    const input = wrapper.get<HTMLInputElement>('input#content_markdown_files');
    const file = markdownFile('notes.pdf', 'Not markdown.', 'application/pdf');

    await textarea.setValue('Keep this pitch.');

    Object.defineProperty(input.element, 'files', {
      configurable: true,
      value: [file],
    });

    await input.trigger('change');
    await flushPromises();

    expect(wrapper.get('[role="status"]').text()).toBe('No markdown files were imported. Choose .md or .markdown files.');
    expect(textarea.element.value).toBe('Keep this pitch.');
  });
});
