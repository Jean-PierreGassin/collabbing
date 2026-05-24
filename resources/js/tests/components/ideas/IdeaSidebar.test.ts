import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import IdeaSidebar from '@/components/ideas/IdeaSidebar.vue';
import type { DomainUser, Idea, IdeaApplication, IdeaCollaboration, IdeaSupporter } from '@/types/domain';

const session = vi.hoisted(() => ({
  isAuthenticated: true,
  routes: {
    login: '/login',
    register: '/register',
  },
}));

vi.mock('@inertiajs/vue3', () => ({
  useForm: () => ({
    delete: vi.fn(),
    post: vi.fn(),
    processing: false,
  }),
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

function application(overrides: Partial<IdeaApplication> = {}): IdeaApplication {
  return {
    id: 1,
    content: 'I can help.',
    contentHtml: '<p>I can help.</p>',
    contributionType: 'frontend',
    contributionTypeDisplay: 'Frontend',
    firstAction: 'Review the first issue.',
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
      destroy: '/applications/1',
      edit: '/applications/1/edit',
      update: '/applications/1',
      approve: '/applications/1/approve',
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

function idea(overrides: Partial<Idea> = {}): Idea {
  return {
    id: 1,
    title: 'Useful idea',
    titleDisplay: 'Useful idea',
    tagline: 'A concise product tagline.',
    summary: 'A summary that belongs on the full idea page.',
    tags: ['design-systems'],
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
    supportersCount: 3,
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

function mountSidebar(props: {
  idea?: Idea;
  collaborator?: IdeaApplication | null;
  applicant?: IdeaApplication | null;
  supporter?: IdeaSupporter | null;
} = {}) {
  return mount(IdeaSidebar, {
    props: {
      idea: props.idea ?? idea(),
      collaborator: props.collaborator,
      applicant: props.applicant,
      supporter: props.supporter,
    },
    global: {
      stubs: {
        UserAvatar: true,
      },
    },
  });
}

describe('IdeaSidebar', () => {
  it('shows start collaborating first with public guidance and readiness context', () => {
    session.isAuthenticated = true;
    const wrapper = mountSidebar({
      idea: idea({
        repository: true,
        collaboration: collaboration({
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
          helpWantedNote: 'Regression coverage and UI review would help.',
          firstContribution: 'Review the first issue.',
          communicationStyle: 'github',
          communicationStyleDisplay: 'GitHub',
          communicationNote: 'Issues and pull requests first.',
          gettingStartedNotesReady: true,
          readinessBadges: {
            applicationsOpen: true,
            firstStepListed: true,
            repoAvailable: true,
            startNotesReady: true,
          },
        }),
      }),
    });

    const headings = wrapper.findAll('h2').map((heading) => heading.text());

    expect(headings[0]).toBe('Start collaborating');
    expect(wrapper.text()).not.toContain('Ready to build');
    expect(wrapper.text()).toContain('Frontend');
    expect(wrapper.text()).toContain('Testing');
    expect(wrapper.text()).toContain('Review the first issue.');
    expect(wrapper.text()).toContain('Applications open');
    expect(wrapper.text()).toContain('GitHub');
    expect(wrapper.text()).toContain('Readiness signals');
    expect(wrapper.text()).toContain('Repo connected');
    expect(wrapper.text()).toContain('Start notes ready');
    expect(wrapper.find('button[aria-label="A small public starting point so someone can understand the first useful action before they apply."]').exists()).toBe(true);
    expect(wrapper.get('a[href="/ideas/1/applications/create"]').text()).toContain('Apply to collaborate');
  });

  it('renders first contribution links through sanitized markdown', () => {
    session.isAuthenticated = true;
    const wrapper = mountSidebar({
      idea: idea({
        collaboration: collaboration({
          firstContribution: 'Review [issue #1](https://example.com/issues/1).',
          firstContributionHtml: '<p>Review <a href="https://example.com/issues/1">issue #1</a>.</p>',
        }),
      }),
    });

    expect(wrapper.get('a[href="https://example.com/issues/1"]').text()).toBe('issue #1');
  });

  it('offers guests sign-in and register actions without hiding public guidance', () => {
    session.isAuthenticated = false;
    const wrapper = mountSidebar({
      idea: idea({
        can: {
          ...idea().can,
          storeApplication: false,
          storeSupporter: false,
          storeComment: false,
        },
        collaboration: collaboration({
          stageDisplay: 'Needs shaping',
          applicationsOpen: true,
        }),
      }),
    });

    expect(wrapper.text()).not.toContain('Needs shaping');
    expect(wrapper.get('a[href="/register?next=%2Fideas%2F1"]').text()).toContain('Register');
    expect(wrapper.get('a[href="/login?next=%2Fideas%2F1"]').text()).toContain('Sign in');
    expect(wrapper.text()).toContain('Sign in to apply, support, or comment.');
    expect(wrapper.text()).not.toContain('Apply to collaborate');
  });

  it('shows closed application guidance without removing support context', () => {
    session.isAuthenticated = true;
    const wrapper = mountSidebar({
      idea: idea({
        can: {
          ...idea().can,
          storeApplication: false,
        },
        collaboration: collaboration({
          applicationsOpen: false,
          applicationsClosedNote: 'Paused while current applications are reviewed.',
          readinessBadges: {
            applicationsOpen: false,
            firstStepListed: false,
            repoAvailable: false,
            startNotesReady: false,
          },
        }),
      }),
    });

    expect(wrapper.text()).toContain('Applications closed');
    expect(wrapper.text()).toContain('Paused while current applications are reviewed.');
    expect(wrapper.text()).toContain('Support and comments remain open while applications are closed.');
    expect(wrapper.text()).toContain('There are 3 people supporting this idea.');
  });

  it('does not tell guests to apply when applications are closed', () => {
    session.isAuthenticated = false;
    const wrapper = mountSidebar({
      idea: idea({
        can: {
          ...idea().can,
          storeApplication: false,
        },
        collaboration: collaboration({
          applicationsOpen: false,
        }),
      }),
    });

    expect(wrapper.text()).toContain('Sign in to support or comment while applications are closed.');
    expect(wrapper.text()).not.toContain('Sign in to apply, support, or comment.');
    expect(wrapper.text()).not.toContain('Register to apply');
  });

  it('does not show owner-only action chrome inside the public sidebar', () => {
    session.isAuthenticated = true;
    const wrapper = mountSidebar({
      idea: idea({
        can: {
          ...idea().can,
          update: true,
        },
      }),
    });

    expect(wrapper.text()).not.toContain('You own this idea');
    expect(wrapper.text()).not.toContain('Owner controls live on the manage page');
    expect(wrapper.find('a[href="/ideas/1/dashboard"]').exists()).toBe(false);
  });

  it('surfaces current applicant and collaborator states as the primary action', () => {
    session.isAuthenticated = true;
    const applicantSidebar = mountSidebar({
      applicant: application({
        thread: {
          messages: [],
          unreadCount: 2,
          hasUnread: true,
          canMessage: true,
          isReadOnly: false,
          readOnlyReason: null,
          routes: {
            read: '/applications/1/read-state',
            store: '/applications/1/messages',
          },
        },
      }),
    });
    const collaboratorSidebar = mountSidebar({
      collaborator: application({
        status: 'approved',
        statusDisplay: 'Collaborating',
      }),
    });

    expect(applicantSidebar.text()).toContain('Application pending');
    expect(applicantSidebar.text()).toContain('Application thread');
    expect(applicantSidebar.text()).toContain('2 new');
    expect(applicantSidebar.get('a[href="/applications/1/edit"]').text()).toContain('Edit application');
    expect(applicantSidebar.find('form[action="/applications/1"]').exists()).toBe(true);
    expect(applicantSidebar.text()).toContain('Public first steps, support, and comments stay available.');
    expect(collaboratorSidebar.text()).toContain('Collaborating');
    expect(collaboratorSidebar.get('a[href="#collaborator-start"]').text()).toContain('View start notes');
  });

  it('keeps accepted collaborator private notes out of the public sidebar', () => {
    session.isAuthenticated = true;
    const wrapper = mountSidebar({
      idea: idea({
        repository: true,
        repositoryActivity: {
          ...idea().repositoryActivity,
          htmlUrl: 'https://github.com/example/repo',
        },
        collaboration: collaboration({
          gettingStartedNotesReady: true,
          gettingStartedNotes: 'Private setup notes.',
          gettingStartedNotesHtml: '<p>Private setup notes.</p>',
          gettingStartedNotesUpdatedAtForHumans: '2 minutes ago',
        }),
      }),
      collaborator: application({
        status: 'approved',
        statusDisplay: 'Collaborating',
        approvalNote: 'Start with issue #1.',
        approvalNoteHtml: '<p>Start with issue #1.</p>',
      }),
    });

    expect(wrapper.text()).toContain('Collaborating');
    expect(wrapper.text()).toContain('View start notes');
    expect(wrapper.text()).not.toContain('Your starting point');
    expect(wrapper.text()).not.toContain('Private setup notes.');
    expect(wrapper.text()).not.toContain('Start with issue #1.');
    expect(wrapper.text()).not.toContain('Approval note');
  });
});
