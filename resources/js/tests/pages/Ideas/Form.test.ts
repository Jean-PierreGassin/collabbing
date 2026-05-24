import { flushPromises, mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import IdeaForm from '@/pages/Ideas/Form.vue';
import type { Idea, IdeaCollaboration } from '@/types/domain';

const session = vi.hoisted(() => ({
  csrfToken: 'test-token',
  errors: {} as Record<string, string>,
  oldInput: {} as Record<string, boolean | number | string | string[] | null>,
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
    collaboration: collaboration(),
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
    pendingApplicationsCount: null,
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

function collaboration(overrides: Partial<IdeaCollaboration> = {}): IdeaCollaboration {
  return {
    stage: null,
    stageDisplay: 'Not decided yet',
    helpWanted: [],
    helpWantedDisplay: [],
    helpWantedNote: null,
    firstContribution: null,
    applicationsOpen: true,
    applicationsClosedNote: null,
    communicationStyle: null,
    communicationStyleDisplay: 'Not decided yet',
    communicationNote: null,
    gettingStartedNotesReady: false,
    gettingStartedNotes: null,
    gettingStartedNotesHtml: null,
    gettingStartedNotesUpdatedAtForHumans: null,
    readinessBadges: {
      applicationsOpen: true,
      firstStepListed: false,
      repoAvailable: false,
      startNotesReady: false,
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

async function fillBasics(wrapper: ReturnType<typeof mountForm>): Promise<void> {
  await wrapper.get<HTMLInputElement>('input#title').setValue('Useful create idea');
  await wrapper.get<HTMLInputElement>('input#tagline').setValue('A sharp tagline');
  await wrapper.get<HTMLTextAreaElement>('textarea#summary').setValue('A short summary for this idea.');
  await wrapper.get<HTMLTextAreaElement>('textarea#content').setValue('A useful pitch.');
}

async function continueToCollaboration(wrapper: ReturnType<typeof mountForm>): Promise<void> {
  await fillBasics(wrapper);
  await clickButton(wrapper, 'Continue');
  await nextTick();
}

async function selectCombobox(id: string, label: string): Promise<void> {
  const button = document.querySelector<HTMLButtonElement>(`button#${id}`);

  if (!button) {
    throw new Error(`Could not find combobox: ${id}`);
  }

  button.click();
  await nextTick();

  const option = Array.from(document.body.querySelectorAll<HTMLButtonElement>('[role="option"]'))
    .find((candidate) => candidate.textContent?.includes(label));

  if (!option) {
    throw new Error(`Could not find option: ${label}`);
  }

  option.click();
  await nextTick();
}

function formData(wrapper: ReturnType<typeof mountForm>): FormData {
  return new FormData(wrapper.get<HTMLFormElement>('form').element);
}

function isHidden(wrapper: ReturnType<typeof mountForm>, selector: string): boolean {
  return wrapper.get(selector).classes().includes('hidden');
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

  it('shows create basics before collaboration setup', () => {
    const wrapper = mountForm();

    expect(isHidden(wrapper, '#idea-form-basics-step')).toBe(false);
    expect(isHidden(wrapper, '#idea-form-collaboration-step')).toBe(true);
    expect(wrapper.get('[aria-label="Idea creation steps"]').text()).toContain('Idea basics');
    expect(wrapper.findAll('button').some((button) => button.text() === 'Share Idea')).toBe(false);
  });

  it('continues to collaboration setup after basics are valid', async () => {
    const wrapper = mountForm();

    await continueToCollaboration(wrapper);

    expect(isHidden(wrapper, '#idea-form-basics-step')).toBe(true);
    expect(isHidden(wrapper, '#idea-form-collaboration-step')).toBe(false);
    expect(wrapper.findAll('button').some((button) => button.text() === 'Share Idea')).toBe(true);
  });

  it('keeps basic values submitted from collaboration setup', async () => {
    const wrapper = mountForm();

    await continueToCollaboration(wrapper);
    await wrapper.get<HTMLInputElement>('input#repository_name').setValue('useful-create-idea');

    const data = formData(wrapper);

    expect(data.getAll('title')).toEqual(['Useful create idea']);
    expect(data.getAll('tagline')).toEqual(['A sharp tagline']);
    expect(data.getAll('summary')).toEqual(['A short summary for this idea.']);
    expect(data.getAll('content')).toEqual(['A useful pitch.']);
    expect(data.get('repository_name')).toBe('useful-create-idea');
  });

  it('does not clear the local draft when moving between create steps', async () => {
    const wrapper = mountForm();

    await fillBasics(wrapper);
    await nextTick();

    const draftBefore = window.localStorage.getItem('collabbing.ideaFormDraft.create');

    await clickButton(wrapper, 'Continue');

    const draftAfter = window.localStorage.getItem('collabbing.ideaFormDraft.create');

    expect(draftBefore).not.toBeNull();
    expect(draftAfter).not.toBeNull();
    expect(JSON.parse(draftAfter ?? '{}')).toMatchObject({
      title: 'Useful create idea',
      content: 'A useful pitch.',
    });
  });

  it('saves collaboration setup fields in the create draft', async () => {
    const wrapper = mountForm();

    await continueToCollaboration(wrapper);
    await wrapper.get<HTMLInputElement>('input[value="frontend"]').setValue(true);
    await wrapper.get<HTMLTextAreaElement>('textarea#first_contribution').setValue('Review the first issue.');
    await wrapper.get<HTMLInputElement>('input#repository_name').setValue('useful-create-idea');
    await nextTick();

    const draft = JSON.parse(window.localStorage.getItem('collabbing.ideaFormDraft.create') ?? '{}') as {
      firstContribution?: string;
      helpWanted?: string[];
      repositoryName?: string;
    };

    expect(draft.helpWanted).toEqual(['frontend']);
    expect(draft.firstContribution).toBe('Review the first issue.');
    expect(draft.repositoryName).toBe('useful-create-idea');
  });

  it('restores a recoverable create draft', async () => {
    window.localStorage.setItem('collabbing.ideaFormDraft.create', JSON.stringify({
      title: 'Draft title',
      tagline: 'Draft tagline',
      communication: 'Discord',
      collaborationStage: 'ready_to_build',
      helpWanted: ['frontend'],
      helpWantedNote: 'Draft help note',
      firstContribution: 'Draft first step',
      applicationsOpen: '0',
      applicationsClosedNote: 'Draft closed note',
      communicationStyle: 'github',
      communicationNote: 'Draft coordination note',
      gettingStartedNotes: 'Draft private notes',
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

    await clickButton(wrapper, 'Continue');

    expect(wrapper.get<HTMLInputElement>('input[value="frontend"]').element.checked).toBe(true);
    expect(wrapper.get<HTMLTextAreaElement>('textarea#first_contribution').element.value).toBe('Draft first step');
    expect(wrapper.get<HTMLInputElement>('input#repository_name').element.value).toBe('draft-repo');
    expect(wrapper.text()).not.toContain('Unsaved draft found');
  });

  it('restores legacy create drafts without collaboration fields', async () => {
    window.localStorage.setItem('collabbing.ideaFormDraft.create', JSON.stringify({
      title: 'Legacy draft title',
      tagline: 'Legacy draft tagline',
      communication: 'Discord',
      tags: 'design',
      repositoryName: 'legacy-draft-repo',
      summary: 'Legacy draft summary',
      content: 'Legacy draft pitch',
      savedAt: '2026-05-24T02:00:00.000Z',
    }));

    const wrapper = mountForm();

    await clickButton(wrapper, 'Restore draft');
    await clickButton(wrapper, 'Continue');

    expect(wrapper.get<HTMLInputElement>('input#repository_name').element.value).toBe('legacy-draft-repo');
    expect(wrapper.get<HTMLInputElement>('input[value="frontend"]').element.checked).toBe(false);
    expect(wrapper.get<HTMLTextAreaElement>('textarea#getting_started_notes').element.value).toBe('');
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

  it('shows collaboration validation errors on step 2', () => {
    session.errors = {
      repository_name: 'Repository names may only contain letters, numbers, dashes, and underscores.',
    };
    session.oldInput = {
      repository_name: 'bad repo',
    };

    const wrapper = mountForm();

    expect(isHidden(wrapper, '#idea-form-basics-step')).toBe(true);
    expect(isHidden(wrapper, '#idea-form-collaboration-step')).toBe(false);
    expect(wrapper.text()).toContain('Repository names may only contain letters');
  });

  it('shows basics validation errors on step 1', () => {
    session.errors = {
      title: 'The title field is required.',
    };
    session.oldInput = {
      repository_name: 'retained-repository',
    };

    const wrapper = mountForm();

    expect(isHidden(wrapper, '#idea-form-basics-step')).toBe(false);
    expect(isHidden(wrapper, '#idea-form-collaboration-step')).toBe(true);
    expect(wrapper.text()).toContain('The title field is required.');
  });

  it('renders and updates the owner readiness checklist on create step 2', async () => {
    const wrapper = mountForm();

    await continueToCollaboration(wrapper);

    const checklist = () => wrapper.get('aside').text();

    expect(checklist()).toContain('Collaboration readiness');
    expect(checklist()).toContain('Set where the idea is at');

    await selectCombobox('collaboration_stage', 'Ready to build');
    await wrapper.get<HTMLInputElement>('input[value="frontend"]').setValue(true);
    await wrapper.get<HTMLTextAreaElement>('textarea#first_contribution').setValue('Review the first issue.');
    await wrapper.get<HTMLInputElement>('input#repository_name').setValue('useful-create-idea');

    const completeLabels = wrapper
      .findAll('aside li span')
      .filter((span) => span.classes().includes('text-foreground'))
      .map((span) => span.text());

    expect(completeLabels).toContain('Set where the idea is at');
    expect(completeLabels).toContain('Choose the help you want');
    expect(completeLabels).toContain('Add a first thing someone can do');
    expect(completeLabels).toContain('Create or connect a repository');
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

  it('keeps edit mode as one sectioned form', () => {
    const wrapper = mountForm({ idea: idea() });

    expect(wrapper.find('[aria-label="Idea creation steps"]').exists()).toBe(false);
    expect(isHidden(wrapper, '#idea-form-basics-step')).toBe(false);
    expect(isHidden(wrapper, '#idea-form-collaboration-step')).toBe(false);
    expect(wrapper.text()).toContain('Idea basics');
    expect(wrapper.text()).toContain('Collaboration setup');
    expect(wrapper.findAll('button').some((button) => button.text() === 'Continue')).toBe(false);
    expect(wrapper.findAll('button').some((button) => button.text() === 'Edit Idea')).toBe(true);
  });

  it('prefills edit collaboration values from idea props', () => {
    const wrapper = mountForm({
      idea: idea({
        collaboration: collaboration({
          stage: 'ready_to_build',
          helpWanted: [
            'backend',
            'testing',
          ],
          helpWantedNote: 'Backend test coverage would help.',
          firstContribution: 'Write a regression test.',
          applicationsOpen: false,
          applicationsClosedNote: 'Closed while reviewing applicants.',
          communicationStyle: 'github',
          communicationNote: 'Use issues first.',
          gettingStartedNotes: 'Private setup notes.',
        }),
        repositoryName: 'prefilled-repo',
      }),
    });

    expect(wrapper.get<HTMLInputElement>('input[value="backend"]').element.checked).toBe(true);
    expect(wrapper.get<HTMLInputElement>('input[value="testing"]').element.checked).toBe(true);
    expect(wrapper.get<HTMLTextAreaElement>('textarea#help_wanted_note').element.value).toBe('Backend test coverage would help.');
    expect(wrapper.get<HTMLTextAreaElement>('textarea#first_contribution').element.value).toBe('Write a regression test.');
    expect(wrapper.get<HTMLInputElement>('input#applications_closed_note').element.value).toBe('Closed while reviewing applicants.');
    expect(wrapper.get<HTMLInputElement>('input#communication_note').element.value).toBe('Use issues first.');
    expect(wrapper.get<HTMLTextAreaElement>('textarea#getting_started_notes').element.value).toBe('Private setup notes.');
    expect(wrapper.get<HTMLInputElement>('input#repository_name').element.value).toBe('prefilled-repo');
    expect(formData(wrapper).get('communication')).toBe('github');
  });
});
