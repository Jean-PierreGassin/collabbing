import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import Apply from '@/pages/Ideas/Apply.vue';
import type { DomainUser, Idea, IdeaApplication, IdeaCollaboration } from '@/types/domain';

const session = vi.hoisted(() => ({
  errors: {} as Record<string, string>,
  oldInput: {} as Record<string, boolean | number | string | string[] | null>,
}));

vi.mock('@/stores/session', () => ({
  useSessionStore: () => session,
}));

function user(overrides: Partial<DomainUser> = {}): DomainUser {
  return {
    id: 1,
    username: 'builder',
    firstName: 'Build',
    lastName: 'Er',
    name: 'Build Er',
    email: null,
    bio: null,
    bioHtml: null,
    githubUsername: 'builder',
    hasGithubToken: true,
    profilePicture: '/avatar.png',
    createdAtFormatted: '20 May - 2026',
    canUpdate: false,
    routes: {
      show: '/users/builder',
      edit: '/users/builder/edit',
      update: '/users/builder',
      githubLogin: '/auth/github',
      githubRevoke: '/auth/github/revoke',
    },
    ...overrides,
  };
}

function collaboration(overrides: Partial<IdeaCollaboration> = {}): IdeaCollaboration {
  return {
    stage: 'ready_to_build',
    stageDisplay: 'Ready to build',
    helpWanted: [
      'frontend',
      'testing',
    ],
    helpWantedDisplay: [
      'Frontend',
      'Testing',
    ],
    helpWantedNote: null,
    firstContribution: 'Review the first issue.',
    applicationsOpen: true,
    applicationsClosedNote: null,
    communicationStyle: 'github',
    communicationStyleDisplay: 'GitHub',
    communicationNote: null,
    gettingStartedNotesReady: false,
    gettingStartedNotes: null,
    gettingStartedNotesHtml: null,
    gettingStartedNotesUpdatedAtForHumans: null,
    readinessBadges: {
      applicationsOpen: true,
      firstStepListed: true,
      repoAvailable: false,
      startNotesReady: false,
    },
    ...overrides,
  };
}

function idea(overrides: Partial<Idea> = {}): Idea {
  return {
    id: 1,
    title: 'Useful idea',
    titleDisplay: 'Useful idea',
    tagline: 'A useful card tagline.',
    summary: 'A useful idea for collaborators.',
    tags: ['design'],
    communication: null,
    collaboration: collaboration(),
    content: 'A focused pitch.',
    contentHtml: '<p>A focused pitch.</p>',
    status: 'open',
    statusDisplay: 'Open',
    repository: false,
    repositoryName: null,
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
    createdAtForHumans: '1 hour ago',
    user: user(),
    supportersCount: 0,
    approvedApplicationsCount: 0,
    pendingApplicationsCount: null,
    collaborators: [],
    hiddenCollaboratorsCount: 0,
    can: {
      update: false,
      storeApplication: true,
      storeSupporter: true,
      deleteApplication: false,
      updateApplication: false,
      storeComment: true,
    },
    routes: {
      show: '/ideas/1',
      edit: '/ideas/1/edit',
      dashboard: '/ideas/1/dashboard',
      update: '/ideas/1',
      applicationsCreate: '/ideas/1/applications/create',
      applicationsStore: '/ideas/1/applications',
      commentsStore: '/ideas/1/comments',
      supportersStore: '/ideas/1/supporters',
      repositoryCreate: '/ideas/1/repository-create',
      repositoryInvite: '/ideas/1/repository-invite',
    },
    ...overrides,
  };
}

function application(overrides: Partial<IdeaApplication> = {}): IdeaApplication {
  return {
    id: 3,
    content: 'I can help with launch planning.',
    contentHtml: '<p>I can help with launch planning.</p>',
    contributionType: 'testing',
    contributionTypeDisplay: 'Testing',
    firstAction: 'Write the first regression test.',
    approvalNote: null,
    approvalNoteHtml: null,
    declineReason: null,
    status: 'pending',
    statusDisplay: 'Pending',
    createdAtForHumans: 'Today',
    user: user({
      id: 2,
      username: 'applicant',
    }),
    thread: null,
    routes: {
      destroy: '/ideas/1/applications/3',
      edit: '/ideas/1/applications/3/edit',
      update: '/ideas/1/applications/3',
      approve: '/ideas/1/applications/3/approve',
    },
    ...overrides,
  };
}

function mountApply(props: { application?: IdeaApplication | null } = {}) {
  return mount(Apply, {
    attachTo: document.body,
    props: {
      idea: idea(),
      ...props,
    },
    global: {
      stubs: {
        CsrfField: true,
      },
    },
  });
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

function formData(wrapper: ReturnType<typeof mountApply>): FormData {
  return new FormData(wrapper.get<HTMLFormElement>('form').element);
}

describe('Ideas/Apply', () => {
  beforeEach(() => {
    session.errors = {};
    session.oldInput = {};
  });

  it('shows recommended owner help while allowing any contribution type', async () => {
    const wrapper = mountApply();

    expect(wrapper.text()).toContain('Owner is looking for');
    expect(wrapper.text()).toContain('Frontend');
    expect(wrapper.text()).toContain('Testing');

    await selectCombobox('contribution_type', 'Frontend - recommended');

    expect(wrapper.get('[role="combobox"]').text()).toContain('Frontend');

    await selectCombobox('contribution_type', 'I can help with anything');

    expect(formData(wrapper).get('contribution_type')).toBe('anything');
  });

  it('submits intent fields and optional context', async () => {
    const wrapper = mountApply();

    expect(wrapper.get<HTMLSelectElement>('select[name="contribution_type"]').element.required).toBe(true);

    await selectCombobox('contribution_type', 'Testing - recommended');
    await wrapper.get<HTMLInputElement>('input#first_action').setValue('Write a regression test.');
    await wrapper.get<HTMLTextAreaElement>('textarea#content').setValue('I can start with Vitest coverage.');

    const data = formData(wrapper);

    expect(wrapper.get('form').attributes('action')).toBe('/ideas/1/applications');
    expect(data.get('contribution_type')).toBe('testing');
    expect(data.get('first_action')).toBe('Write a regression test.');
    expect(data.get('content')).toBe('I can start with Vitest coverage.');
  });

  it('prefills pending applications for editing', () => {
    const wrapper = mountApply({
      application: application(),
    });

    expect(wrapper.get('form').attributes('action')).toBe('/ideas/1/applications/3');
    expect(wrapper.text()).toContain('Edit application to Useful idea');
    expect(formData(wrapper).get('_method')).toBe('PUT');
    expect(formData(wrapper).get('contribution_type')).toBe('testing');
    expect(wrapper.get<HTMLInputElement>('input#first_action').element.value).toBe('Write the first regression test.');
    expect(wrapper.get<HTMLTextAreaElement>('textarea#content').element.value).toBe('I can help with launch planning.');
  });
});
