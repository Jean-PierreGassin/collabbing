import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import IdeaCollaboratorStartPanel from '@/components/ideas/IdeaCollaboratorStartPanel.vue';
import type { DomainUser, Idea, IdeaApplication, IdeaCollaboration } from '@/types/domain';

vi.mock('@/stores/session', () => ({
  useSessionStore: () => ({
    csrfToken: 'test-token',
  }),
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
    supportersCount: 0,
    approvedApplicationsCount: 1,
    pendingApplicationsCount: null,
    collaborators: [],
    hiddenCollaboratorsCount: 0,
    can: {
      update: false,
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
    id: 1,
    content: 'I can help.',
    contentHtml: '<p>I can help.</p>',
    contributionType: 'frontend',
    contributionTypeDisplay: 'Frontend',
    firstAction: 'Review the first issue.',
    approvalNote: null,
    approvalNoteHtml: null,
    declineReason: null,
    status: 'approved',
    statusDisplay: 'Collaborating',
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

function mountPanel(props: {
  idea?: Idea;
  collaborator?: IdeaApplication;
} = {}) {
  return mount(IdeaCollaboratorStartPanel, {
    props: {
      idea: props.idea ?? idea(),
      collaborator: props.collaborator ?? application(),
    },
    global: {
      stubs: {
        MarkdownContent: {
          props: ['html'],
          template: '<div class="markdown-content" v-html="html" />',
        },
      },
    },
  });
}

describe('IdeaCollaboratorStartPanel', () => {
  it('shows accepted collaborator start notes and approval note in the main workflow section', () => {
    const wrapper = mountPanel({
      idea: idea({
        collaboration: collaboration({
          gettingStartedNotesReady: true,
          gettingStartedNotes: 'Private setup notes.',
          gettingStartedNotesHtml: '<p>Private setup notes.</p>',
          gettingStartedNotesUpdatedAtForHumans: '2 minutes ago',
        }),
      }),
      collaborator: application({
        approvalNote: 'Start with issue #1.',
        approvalNoteHtml: '<p>Start with issue #1.</p>',
      }),
    });

    expect(wrapper.attributes('id')).toBe('collaborator-start');
    expect(wrapper.text()).toContain('Your starting point');
    expect(wrapper.text()).toContain('Private setup notes.');
    expect(wrapper.text()).toContain('Updated 2 minutes ago.');
    expect(wrapper.text()).toContain('Start with issue #1.');
  });

  it('uses a reveal step for leaving collaboration', async () => {
    const wrapper = mountPanel();

    expect(wrapper.find('textarea[name="exit_reason"]').exists()).toBe(false);

    await wrapper.get('button').trigger('click');

    expect(wrapper.get('form[action="/applications/1"] input[name="_method"]').attributes('value')).toBe('DELETE');
    expect(wrapper.get('textarea[name="exit_reason"]').attributes('maxlength')).toBe('1200');
    expect(wrapper.get('form[action="/applications/1"] button[type="submit"]').text()).toContain('Confirm leave');
    expect(wrapper.text()).toContain('Cancel');
  });

  it('shows an honest empty state when accepted collaborator notes are missing', () => {
    const wrapper = mountPanel();

    expect(wrapper.text()).toContain('The owner has not added private start notes yet.');
    expect(wrapper.text()).not.toContain('Approval note');
  });
});
