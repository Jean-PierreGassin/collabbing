import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import Manage from '@/pages/Ideas/Manage.vue';
import type { DomainUser, Idea, IdeaApplication, IdeaCollaboration, Paginator } from '@/types/domain';

vi.mock('@inertiajs/vue3', () => ({
  Link: {
    props: ['href'],
    template: '<a :href="href"><slot /></a>',
  },
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

function idea(): Idea {
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
    can: {
      update: true,
      storeApplication: false,
      storeSupporter: false,
      deleteApplication: false,
      updateApplication: false,
      storeComment: true,
    },
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

function mountManage(applications: IdeaApplication[] = [], collaborators: IdeaApplication[] = []) {
  return mount(Manage, {
    attachTo: document.body,
    props: {
      idea: idea(),
      applications: paginator(applications),
      collaborators: paginator(collaborators),
    },
    global: {
      stubs: {
        CsrfField: true,
        IdeaSidebar: {
          template: '<aside data-testid="idea-sidebar"></aside>',
        },
        MarkdownContent: {
          props: ['html'],
          template: '<div v-html="html"></div>',
        },
        MethodField: true,
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
