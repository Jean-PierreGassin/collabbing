import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import Manage from '@/pages/Ideas/Manage.vue';
import type { DomainUser, Idea, IdeaApplication, IdeaCollaboration, Paginator } from '@/types/domain';

const inertiaPage = vi.hoisted(() => ({
  props: {
    flash: {
      status: null,
      errors: [],
      repositoryInvitePrompt: false,
      repositoryAccessPrompt: false,
    },
  },
}));
const session = vi.hoisted(() => ({
  user: {
    id: 1,
  },
}));

vi.mock('@inertiajs/vue3', () => ({
  Link: {
    props: ['href'],
    template: '<a :href="href"><slot /></a>',
  },
  usePage: () => inertiaPage,
}));

vi.mock('@/stores/session', () => ({
  useSessionStore: () => session,
}));

function paginator<T>(items: T[] = []): Paginator<T> {
  return {
    currentPage: 1,
    items,
    lastPage: 1,
    nextPageUrl: null,
    previousPageUrl: null,
  };
}

function user(overrides: Partial<DomainUser> = {}): DomainUser {
  return {
    id: 1,
    username: 'taylor',
    firstName: 'Taylor',
    lastName: 'Stone',
    name: 'Taylor Stone',
    email: 'taylor@example.com',
    bio: null,
    bioHtml: null,
    githubUsername: null,
    hasGithubToken: false,
    profilePicture: '/avatar.png',
    createdAtFormatted: 'Today',
    canUpdate: false,
    routes: {
      show: '/users/taylor',
      edit: '/users/taylor/edit',
      update: '/users/taylor',
      githubLogin: '/auth/github',
      githubRevoke: '/auth/github/revoke',
    },
    ...overrides,
  };
}

function permissions(overrides: Partial<Idea['can']> = {}): Idea['can'] {
  return {
    update: true,
    storeApplication: false,
    storeSupporter: false,
    deleteApplication: false,
    updateApplication: false,
    storeComment: true,
    ...overrides,
  };
}

function idea(overrides: Partial<Idea> = {}): Idea {
  return {
    id: 1,
    title: 'Build a better dashboard',
    titleDisplay: 'Build a better dashboard',
    tagline: 'A focused dashboard card line',
    summary: 'A focused idea',
    tags: ['dashboard'],
    communication: null,
    collaboration: collaboration(),
    content: '',
    contentHtml: '',
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
    createdAtForHumans: 'Today',
    user: user(),
    supportersCount: 0,
    approvedApplicationsCount: 0,
    pendingApplicationsCount: 0,
    collaborators: [],
    hiddenCollaboratorsCount: 0,
    can: permissions(),
    routes: {
      show: '/ideas/1',
      edit: '/ideas/1/edit',
      dashboard: '/ideas/1/dashboard',
      update: '/ideas/1',
      applicationsCreate: '/ideas/1/apply',
      applicationsStore: '/ideas/1/applications',
      commentsStore: '/ideas/1/comments',
      supportersStore: '/ideas/1/supporters',
      repositoryCreate: '/ideas/1/repository',
      repositoryInvite: '/ideas/1/repository/invite',
    },
    ...overrides,
  };
}

function application(overrides: Partial<IdeaApplication> = {}): IdeaApplication {
  return {
    id: 3,
    content: 'I can help with useful tests.',
    contentHtml: '<p>I can help with useful tests.</p>',
    contributionType: 'testing',
    contributionTypeDisplay: 'Testing',
    firstAction: 'Write the first regression test.',
    approvalNote: null,
    approvalNoteHtml: null,
    declineReason: null,
    status: 'pending',
    statusDisplay: 'Pending',
    createdAtForHumans: '5 minutes ago',
    user: user({
      id: 2,
      username: 'applicant',
      firstName: 'Applied',
      lastName: 'User',
      name: 'Applied User',
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

function applicationThread(): NonNullable<IdeaApplication['thread']> {
  return {
    messages: [{
      id: 5,
      type: 'message',
      body: 'Could you clarify the testing scope?',
      bodyHtml: '<p>Could you clarify the testing scope?</p>',
      isSystem: false,
      occurredAtForHumans: '2 minutes ago',
      user: user({
        id: 2,
        name: 'Applied User',
      }),
    }],
    unreadCount: 1,
    hasUnread: true,
    canMessage: true,
    isReadOnly: false,
    readOnlyReason: null,
    routes: {
      read: '/ideas/1/applications/3/read-state',
      store: '/ideas/1/applications/3/messages',
    },
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

function mountManage(
  applications: IdeaApplication[] = [],
  collaborators: IdeaApplication[] = [],
  ideaOverrides: Partial<Idea> = {},
) {
  return mount(Manage, {
    attachTo: document.body,
    props: {
      idea: idea(ideaOverrides),
      applications: paginator(applications),
      collaborators: paginator(collaborators),
    },
    global: {
      stubs: {
        CsrfField: true,
        MarkdownContent: {
          props: ['html'],
          template: '<div v-html="html"></div>',
        },
        MethodField: {
          props: ['method'],
          template: '<input type="hidden" name="_method" :value="method" />',
        },
        PaginationLinks: {
          props: ['label'],
          template: '<nav :aria-label="label"></nav>',
        },
      },
    },
  });
}

describe('Idea management tabs', () => {
  beforeEach(() => {
    inertiaPage.props.flash.repositoryInvitePrompt = false;
    inertiaPage.props.flash.repositoryAccessPrompt = false;
    window.history.pushState({}, '', '/ideas/1/dashboard');
  });

  it('renders management sections as accessible tabs', () => {
    const wrapper = mountManage();
    const tabs = wrapper.findAll('[role="tab"]');

    expect(wrapper.text()).toContain('Connect code host to create a repository');
    expect(wrapper.text()).not.toContain('Link GitHub to create a repository');
    expect(wrapper.get('[role="tablist"]').attributes('aria-label')).toBe('Idea management sections');
    expect(tabs).toHaveLength(2);
    expect(tabs[0].attributes('id')).toBe('manage-applications-tab');
    expect(tabs[0].attributes('aria-controls')).toBe('manage-applications-panel');
    expect(tabs[0].attributes('aria-selected')).toBe('true');
    expect(tabs[0].attributes('aria-pressed')).toBeUndefined();
    expect(tabs[1].attributes('aria-selected')).toBe('false');

    const panel = wrapper.get('[role="tabpanel"]');

    expect(panel.attributes('id')).toBe('manage-applications-panel');
    expect(panel.attributes('aria-labelledby')).toBe('manage-applications-tab');
  });

  it('shows the owner readiness checklist on the manage page', () => {
    const wrapper = mountManage([], [], {
      user: user({
        hasGithubToken: true,
      }),
    });

    expect(wrapper.text()).toContain('Collaboration readiness');
    expect(wrapper.text()).toContain('Set where the idea is at');
    expect(wrapper.text()).toContain('Choose the help you want');
    expect(wrapper.text()).toContain('Create or connect a repository');
    expect(wrapper.findAll('a[href="/ideas/1/edit#collaboration_stage"]').some((link) => link.text().includes('Set'))).toBe(true);
    expect(wrapper.findAll('a[href="/ideas/1/edit#help_wanted"]').some((link) => link.text().includes('Choose'))).toBe(true);
    expect(wrapper.findAll('a[href="/ideas/1/repository"]').some((link) => link.text().includes('Create'))).toBe(true);
  });

  it('marks completed readiness items without action links', () => {
    const wrapper = mountManage([], [], {
      repository: true,
      collaboration: collaboration({
        stage: 'ready_to_build',
        helpWanted: ['frontend'],
        firstContribution: 'Review the first issue.',
        communicationStyle: 'github',
        gettingStartedNotesReady: true,
      }),
    });

    const checklist = wrapper.get('aside').text();

    expect(checklist).toContain('Collaboration readiness');
    expect(wrapper.findAll('aside a[href^="/ideas/1/edit#"]')).toHaveLength(0);
    expect(wrapper.findAll('aside a[href="/ideas/1/repository"]')).toHaveLength(0);
  });

  it('switches panels when a tab is clicked', async () => {
    const wrapper = mountManage();

    await wrapper.get('#manage-collaborators-tab').trigger('click');

    expect(wrapper.get('#manage-collaborators-tab').attributes('aria-selected')).toBe('true');
    expect(wrapper.get('#manage-applications-tab').attributes('tabindex')).toBe('-1');
    expect(wrapper.get('[role="tabpanel"]').attributes('id')).toBe('manage-collaborators-panel');
    expect(wrapper.text()).toContain('No collaborators have joined yet.');
  });

  it('shows contribution intent on application review cards', () => {
    const wrapper = mountManage([application()]);

    expect(wrapper.text()).toContain("Applied User's Application");
    expect(wrapper.text()).toContain('Contribution type');
    expect(wrapper.text()).toContain('Testing');
    expect(wrapper.text()).toContain('First action');
    expect(wrapper.text()).toContain('Write the first regression test.');
    expect(wrapper.text()).toContain('I can help with useful tests.');
    expect(wrapper.text()).toContain('Submitted 5 minutes ago');
  });

  it('shows application threads on review cards', () => {
    const wrapper = mountManage([application({
      thread: applicationThread(),
    })]);

    expect(wrapper.text()).toContain('Application thread');
    expect(wrapper.text()).toContain('Could you clarify the testing scope?');
    expect(wrapper.text()).toContain('1 new');
    expect(wrapper.find('form[action="/ideas/1/applications/3/messages"]').exists()).toBe(true);
  });

  it('shows owner decision controls with private notes', async () => {
    const wrapper = mountManage([application()], [], {
      can: permissions({
        deleteApplication: true,
        updateApplication: true,
      }),
    });

    expect(wrapper.text()).toContain('Private start notes are missing.');
    expect(wrapper.get('form[action="/ideas/1/applications/3/approve"]').attributes('method')).toBe('POST');
    expect(wrapper.get('form[action="/ideas/1/applications/3/approve"] input[name="_method"]').attributes('value')).toBe('PUT');
    expect(wrapper.get('textarea[name="approval_note"]').attributes('maxlength')).toBe('1200');

    const declineButton = wrapper
      .findAll('button')
      .find((button) => button.text().includes('Decline application'));

    expect(declineButton).toBeDefined();
    expect(declineButton!.attributes('aria-expanded')).toBe('false');
    expect(wrapper.find('form[action="/ideas/1/applications/3"] textarea[name="decline_reason"]').exists()).toBe(false);

    await declineButton!.trigger('click');

    expect(declineButton!.attributes('aria-expanded')).toBe('true');
    expect(wrapper.get('form[action="/ideas/1/applications/3"]').attributes('method')).toBe('POST');
    expect(wrapper.get('form[action="/ideas/1/applications/3"] input[name="_method"]').attributes('value')).toBe('DELETE');
    expect(wrapper.get('textarea[name="decline_reason"]').attributes('maxlength')).toBe('1200');
    expect(wrapper.get('form[action="/ideas/1/applications/3"] button[type="submit"]').text()).toContain('Confirm decline');
    expect(wrapper.text()).toContain('Cancel');
  });

  it('hides the missing notes warning when start notes are ready', () => {
    const wrapper = mountManage([application()], [], {
      can: permissions({
        updateApplication: true,
      }),
      collaboration: collaboration({
        gettingStartedNotesReady: true,
      }),
    });

    expect(wrapper.text()).not.toContain('Private start notes are missing.');
  });

  it('shows a repository invite prompt after approval', () => {
    inertiaPage.props.flash.repositoryInvitePrompt = true;

    const wrapper = mountManage([], [], {
      repository: true,
      user: user({
        hasGithubToken: true,
      }),
    });

    expect(wrapper.text()).toContain('Repository is connected.');
    expect(wrapper.findAll('form[action="/ideas/1/repository/invite"]')).toHaveLength(1);
  });

  it('links owners back to the code host before inviting collaborators', () => {
    inertiaPage.props.flash.repositoryInvitePrompt = true;

    const wrapper = mountManage([], [], {
      repository: true,
      user: user({
        hasGithubToken: false,
      }),
      routes: {
        ...idea().routes,
        repositoryInvite: '/auth/github',
      },
    });

    expect(wrapper.text()).toContain('Connect your code host to invite approved collaborators.');
    expect(wrapper.findAll('form[action="/ideas/1/repository/invite"]')).toHaveLength(0);
    expect(wrapper.findAll('a[href="/auth/github"]')).toHaveLength(1);
    expect(wrapper.find('a[href="/auth/github"]').text()).toContain('Connect code host');
  });

  it('shows repository access cleanup prompt after collaborator exits', () => {
    inertiaPage.props.flash.repositoryAccessPrompt = true;

    const wrapper = mountManage([], [], {
      repository: true,
    });

    expect(wrapper.text()).toContain('Review repository access.');
    expect(wrapper.text()).toContain('left or was removed');
  });

  it('uses a confirm step with private reason when removing collaborators', async () => {
    const collaborator = application({
      status: 'approved',
      statusDisplay: 'Collaborating',
    });
    const wrapper = mountManage([], [collaborator], {
      can: permissions({
        deleteApplication: true,
      }),
    });

    await wrapper.get('#manage-collaborators-tab').trigger('click');

    const removeButton = wrapper
      .findAll('button')
      .find((button) => button.text().includes('Remove collaborator'));

    expect(removeButton).toBeDefined();
    expect(removeButton!.attributes('aria-expanded')).toBe('false');
    expect(wrapper.find('form[action="/ideas/1/applications/3"] textarea[name="exit_reason"]').exists()).toBe(false);

    await removeButton!.trigger('click');

    expect(removeButton!.attributes('aria-expanded')).toBe('true');
    expect(wrapper.get('form[action="/ideas/1/applications/3"] input[name="_method"]').attributes('value')).toBe('DELETE');
    expect(wrapper.get('textarea[name="exit_reason"]').attributes('maxlength')).toBe('1200');
    expect(wrapper.get('form[action="/ideas/1/applications/3"] button[type="submit"]').text()).toContain('Confirm remove');
  });

  it('preserves the collaborator tab intent from the query string', () => {
    window.history.pushState({}, '', '/ideas/1/dashboard?collaborators=1');

    const wrapper = mountManage();

    expect(wrapper.get('#manage-collaborators-tab').attributes('aria-selected')).toBe('true');
    expect(wrapper.get('[role="tabpanel"]').attributes('id')).toBe('manage-collaborators-panel');
  });

  it('supports end key tab activation', async () => {
    const wrapper = mountManage();

    await wrapper.get('#manage-applications-tab').trigger('keydown', { key: 'End' });
    await nextTick();

    expect(wrapper.get('#manage-collaborators-tab').attributes('aria-selected')).toBe('true');
    expect(document.activeElement?.id).toBe('manage-collaborators-tab');
  });
});
