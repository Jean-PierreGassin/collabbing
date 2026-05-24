import { flushPromises, mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import IdeaForm from '@/pages/Ideas/Form.vue';
import type { Idea } from '@/types/domain';

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

function idea(overrides: Partial<Idea> = {}): Idea {
  return {
    id: 7,
    title: 'Existing idea',
    titleDisplay: 'Existing idea',
    tagline: 'Existing tagline',
    summary: 'Existing summary',
    tags: ['design'],
    communication: 'Slack',
    content: 'Existing pitch.',
    contentHtml: '<p>Existing pitch.</p>',
    status: 'open',
    statusDisplay: 'Open',
    repository: false,
    repositoryName: 'existing-repo',
    repositoryActivity: {
      htmlUrl: null,
      defaultBranch: null,
      isMissing: false,
      openIssuesCount: 0,
      stargazersCount: 0,
      forksCount: 0,
      lastPushedAtForHumans: null,
      lastSyncedAtForHumans: null,
      latestCommitSha: null,
      latestCommitShortSha: null,
      latestCommitMessage: null,
      latestCommitAuthor: null,
      events: [],
    },
    createdAtForHumans: 'Today',
    user: {
      id: 1,
      username: 'builder',
      firstName: 'Build',
      lastName: 'Er',
      name: 'Build Er',
      email: null,
      bio: null,
      bioHtml: null,
      githubUsername: null,
      hasGithubToken: false,
      profilePicture: '/avatar.png',
      createdAtFormatted: 'Today',
      canUpdate: false,
      routes: {
        show: '/users/builder',
        edit: '/users/builder/edit',
        update: '/users/builder',
        githubLogin: '/auth/github',
        githubRevoke: '/auth/github/revoke',
      },
    },
    supportersCount: 0,
    approvedApplicationsCount: 0,
    collaborators: [],
    hiddenCollaboratorsCount: 0,
    can: {
      update: true,
      storeApplication: false,
      storeSupporter: false,
      deleteApplication: false,
      updateApplication: false,
      storeComment: true,
    },
    routes: {
      show: '/ideas/7',
      edit: '/ideas/7/edit',
      dashboard: '/ideas/7/dashboard',
      update: '/ideas/7',
      applicationsCreate: '/ideas/7/applications/create',
      applicationsStore: '/ideas/7/applications',
      commentsStore: '/ideas/7/comments',
      supportersStore: '/ideas/7/supporters',
      repositoryCreate: '/ideas/7/repository-create',
      repositoryInvite: '/ideas/7/repository-invite',
    },
    ...overrides,
  };
}

function mountForm(props: { idea?: Idea } = {}) {
  return mount(IdeaForm, {
    attachTo: document.body,
    props,
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

async function clickButton(wrapper: ReturnType<typeof mountForm>, label: string): Promise<void> {
  const button = wrapper
    .findAll('button')
    .find((candidate) => candidate.text().includes(label));

  if (!button) {
    throw new Error(`Could not find button: ${label}`);
  }

  await button.trigger('click');
}

describe('Ideas/Form', () => {
  beforeEach(() => {
    session.errors = {};
    session.oldInput = {};
    window.localStorage.clear();
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

    const statuses = wrapper.findAll('[role="status"]');

    expect(statuses.some((status) => status.text() === 'Imported 1 markdown file.')).toBe(true);
    expect(statuses.every((status) => status.attributes('aria-live') === 'polite')).toBe(true);
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

    expect(wrapper.findAll('[role="status"]').some((status) => status.text() === 'No markdown files were imported. Choose .md or .markdown files.')).toBe(true);
    expect(textarea.element.value).toBe('Keep this pitch.');
  });

  it('saves changed create form values as a local draft', async () => {
    const wrapper = mountForm();

    await wrapper.get<HTMLInputElement>('input#title').setValue('Recovered idea');
    await wrapper.get<HTMLTextAreaElement>('textarea#content').setValue('Recovered pitch');
    await nextTick();

    const draft = JSON.parse(window.localStorage.getItem('collabbing.ideaFormDraft.create') ?? '{}') as Record<string, string>;

    expect(wrapper.text()).toContain('Draft saved locally.');
    expect(draft.title).toBe('Recovered idea');
    expect(draft.content).toBe('Recovered pitch');
  });

  it('restores a recoverable create draft', async () => {
    window.localStorage.setItem('collabbing.ideaFormDraft.create', JSON.stringify({
      title: 'Draft title',
      tagline: 'Draft tagline',
      communication: 'Discord',
      tags: 'design, workflow',
      repositoryName: 'draft-repo',
      summary: 'Draft summary',
      content: 'Draft pitch',
      savedAt: '2026-05-24T02:00:00.000Z',
    }));

    const wrapper = mountForm();

    expect(wrapper.text()).toContain('Unsaved draft found');

    await clickButton(wrapper, 'Restore draft');

    expect(wrapper.get<HTMLInputElement>('input#title').element.value).toBe('Draft title');
    expect(wrapper.get<HTMLTextAreaElement>('textarea#content').element.value).toBe('Draft pitch');
    expect(wrapper.text()).not.toContain('Unsaved draft found');
  });

  it('discards a recoverable create draft', async () => {
    window.localStorage.setItem('collabbing.ideaFormDraft.create', JSON.stringify({
      title: 'Draft title',
      tagline: 'Draft tagline',
      communication: 'Discord',
      tags: 'design, workflow',
      repositoryName: 'draft-repo',
      summary: 'Draft summary',
      content: 'Draft pitch',
      savedAt: '2026-05-24T02:00:00.000Z',
    }));

    const wrapper = mountForm();

    await clickButton(wrapper, 'Discard');

    expect(window.localStorage.getItem('collabbing.ideaFormDraft.create')).toBeNull();
    expect(wrapper.text()).not.toContain('Unsaved draft found');
  });

  it('ignores recoverable drafts when validation old input exists', () => {
    window.localStorage.setItem('collabbing.ideaFormDraft.create', JSON.stringify({
      title: 'Draft title',
      tagline: 'Draft tagline',
      communication: 'Discord',
      tags: 'design',
      repositoryName: 'draft-repo',
      summary: 'Draft summary',
      content: 'Draft pitch',
      savedAt: '2026-05-24T02:00:00.000Z',
    }));
    session.oldInput = {
      title: 'Server title',
      content: 'Server pitch',
    };

    const wrapper = mountForm();

    expect(wrapper.text()).not.toContain('Unsaved draft found');
    expect(wrapper.get<HTMLInputElement>('input#title').element.value).toBe('Server title');
    expect(wrapper.get<HTMLTextAreaElement>('textarea#content').element.value).toBe('Server pitch');
  });

  it('uses idea-specific draft keys when editing', async () => {
    const wrapper = mountForm({ idea: idea() });

    await wrapper.get<HTMLInputElement>('input#title').setValue('Edited draft title');
    await nextTick();

    expect(window.localStorage.getItem('collabbing.ideaFormDraft.create')).toBeNull();
    expect(JSON.parse(window.localStorage.getItem('collabbing.ideaFormDraft.edit.7') ?? '{}')).toMatchObject({
      title: 'Edited draft title',
    });
  });
});
