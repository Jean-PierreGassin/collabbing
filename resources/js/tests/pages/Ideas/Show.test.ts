import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import Show from '@/pages/Ideas/Show.vue';
import type {
  DomainUser,
  Idea,
  IdeaApplication,
  IdeaCollaboration,
  IdeaComment,
  IdeaSupporter,
  Paginator,
} from '@/types/domain';

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
    ...overrides,
  };
}

function collaboration(overrides: Partial<IdeaCollaboration> = {}): IdeaCollaboration {
  return {
    stage: 'ready_to_build',
    stageDisplay: 'Ready to build',
    helpWanted: ['testing'],
    helpWantedDisplay: ['Testing'],
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
    repositoryAccessReviewNeeded: false,
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
    pendingApplicationsCount: null,
    collaborators: [],
    hiddenCollaboratorsCount: 0,
    can: {
      update: false,
      storeApplication: false,
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

function application(): IdeaApplication {
  return {
    id: 3,
    content: 'I can help.',
    contentHtml: '<p>I can help.</p>',
    contributionType: 'testing',
    contributionTypeDisplay: 'Testing',
    firstAction: 'Write a regression test.',
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
    thread: {
      messages: [],
      unreadCount: 0,
      hasUnread: false,
      canMessage: true,
      isReadOnly: false,
      readOnlyReason: null,
      routes: {
        read: '/ideas/1/applications/3/read-state',
        store: '/ideas/1/applications/3/messages',
      },
    },
    routes: {
      destroy: '/ideas/1/applications/3',
      edit: '/ideas/1/applications/3/edit',
      update: '/ideas/1/applications/3',
      approve: '/ideas/1/applications/3/approve',
    },
  };
}

function mountShow(
  applicant: IdeaApplication | null = null,
  historicalApplication: IdeaApplication | null = null,
  ideaOverrides: Partial<Idea> = {},
) {
  return mount(Show, {
    props: {
      idea: idea(ideaOverrides),
      comments: paginator<IdeaComment>(),
      collaborator: null,
      applicant,
      historicalApplication,
      supporter: null as IdeaSupporter | null,
    },
    global: {
      stubs: {
        CommentList: {
          props: ['canStoreComment'],
          template: '<section data-testid="comments">{{ canStoreComment ? "can comment" : "read only comments" }}</section>',
        },
        IdeaApplicationThread: {
          props: [
            'application',
            'title',
          ],
          template: '<section id="application-thread">{{ title }}</section>',
        },
        IdeaCard: {
          template: '<article data-testid="idea-card"></article>',
        },
        IdeaSidebar: {
          template: '<aside data-testid="idea-sidebar"></aside>',
        },
        MarkdownTableOfContents: true,
      },
    },
  });
}

describe('Ideas/Show', () => {
  it('renders the private application thread in the main column for applicants', () => {
    const wrapper = mountShow(application());

    expect(wrapper.get('#application-thread').text()).toContain('Your application thread');
    expect(wrapper.find('[data-testid="comments"]').exists()).toBe(true);
  });

  it('does not render a thread for public idea viewers', () => {
    const wrapper = mountShow();

    expect(wrapper.find('#application-thread').exists()).toBe(false);
  });

  it('keeps public comments visible when the viewer cannot comment', () => {
    const wrapper = mountShow(null, null, {
      can: {
        ...idea().can,
        storeComment: false,
      },
    });

    expect(wrapper.get('[data-testid="comments"]').text()).toContain('read only comments');
  });

  it('renders historical application threads read only', () => {
    const historicalApplication = application();

    historicalApplication.status = 'removed';
    historicalApplication.statusDisplay = 'Removed from collaboration';
    historicalApplication.thread = {
      ...historicalApplication.thread!,
      canMessage: false,
      isReadOnly: true,
      readOnlyReason: 'This application thread is read-only after a final decision.',
    };

    const wrapper = mountShow(null, historicalApplication);

    expect(wrapper.get('#application-thread').text()).toContain('Your application thread');
  });
});
