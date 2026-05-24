import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import IdeaActionRail from '@/components/ideas/IdeaActionRail.vue';
import type { DomainUser, Idea, IdeaApplication } from '@/types/domain';

function user(): DomainUser {
  return {
    id: 1,
    username: 'builder',
    firstName: 'Taylor',
    lastName: 'Stone',
    name: 'Taylor Stone',
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
    supportersCount: 3,
    approvedApplicationsCount: 1,
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

function application(status: string): IdeaApplication {
  return {
    id: 1,
    content: '',
    contentHtml: '',
    status,
    createdAtForHumans: 'Today',
    user: user(),
    routes: {
      destroy: '/ideas/1/applications/1',
      approve: '/ideas/1/applications/1',
    },
  };
}

describe('IdeaActionRail', () => {
  it('links visitors to apply when collaboration is available', () => {
    const wrapper = mount(IdeaActionRail, {
      props: {
        idea: idea(),
        applicant: null,
        collaborator: null,
      },
    });

    expect(wrapper.text()).toContain('1 collaborator');
    expect(wrapper.text()).toContain('3 supporters');
    expect(wrapper.get('a[href="/ideas/1/applications/create"]').text()).toContain('Apply to collaborate');
  });

  it('shows pending state instead of another apply link', () => {
    const wrapper = mount(IdeaActionRail, {
      props: {
        idea: idea(),
        applicant: application('pending'),
        collaborator: null,
      },
    });

    expect(wrapper.text()).toContain('Application pending');
    expect(wrapper.find('a[href="/ideas/1/applications/create"]').exists()).toBe(false);
  });

  it('shows collaborator state before application actions', () => {
    const wrapper = mount(IdeaActionRail, {
      props: {
        idea: idea(),
        applicant: application('pending'),
        collaborator: application('approved'),
      },
    });

    expect(wrapper.text()).toContain('Collaborator');
    expect(wrapper.text()).not.toContain('Application pending');
    expect(wrapper.find('a[href="/ideas/1/applications/create"]').exists()).toBe(false);
  });

  it('shows owner management actions', () => {
    const wrapper = mount(IdeaActionRail, {
      props: {
        idea: idea({
          can: {
            update: true,
            storeApplication: false,
            storeSupporter: false,
            deleteApplication: false,
            updateApplication: false,
            storeComment: true,
          },
        }),
        applicant: null,
        collaborator: null,
      },
    });

    expect(wrapper.get('a[href="/ideas/1/dashboard"]').text()).toContain('Manage');
    expect(wrapper.get('a[href="/ideas/1/edit"]').text()).toContain('Edit');
    expect(wrapper.find('a[href="/ideas/1/applications/create"]').exists()).toBe(false);
  });
});
