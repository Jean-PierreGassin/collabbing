import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import IdeaSidebar from '@/components/ideas/IdeaSidebar.vue';
import type { Idea, IdeaRepositoryActivity, RepositoryEvent } from '@/types/domain';

function repositoryActivity(overrides: Partial<IdeaRepositoryActivity> = {}): IdeaRepositoryActivity {
  return {
    htmlUrl: 'https://github.com/example/collab-brief',
    defaultBranch: 'main',
    isMissing: false,
    openIssuesCount: 4,
    stargazersCount: 12,
    forksCount: 2,
    lastPushedAtForHumans: '2 hours ago',
    lastSyncedAtForHumans: '10 minutes ago',
    latestCommitSha: 'abc123456789',
    latestCommitShortSha: 'abc1234',
    latestCommitMessage: 'Tighten onboarding flow',
    latestCommitAuthor: 'octocat',
    events: [],
    ...overrides,
  };
}

function repositoryEvent(id: number, type: string, summary: string): RepositoryEvent {
  return {
    id,
    type,
    summary,
    occurredAtForHumans: `${id} hours ago`,
  };
}

function idea(overrides: Partial<Idea> = {}): Idea {
  return {
    id: 42,
    title: 'Collab Brief',
    titleDisplay: 'Collab Brief',
    summary: 'A useful collaboration brief.',
    communication: null,
    content: 'Content',
    contentHtml: '<p>Content</p>',
    status: 'active',
    statusDisplay: 'Active',
    repository: true,
    repositoryName: 'collab-brief',
    repositoryActivity: repositoryActivity(),
    createdAtForHumans: '1 day ago',
    user: {
      id: 9,
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
      createdAtFormatted: 'May 2026',
      canUpdate: false,
      routes: {
        show: '/users/builder',
        edit: '/profile/edit',
        update: '/profile',
        githubLogin: '/github/login',
        githubRevoke: '/github/revoke',
      },
    },
    supportersCount: 0,
    approvedApplicationsCount: 0,
    collaborators: [],
    hiddenCollaboratorsCount: 0,
    can: {
      update: false,
      storeApplication: false,
      storeSupporter: false,
      deleteApplication: false,
      updateApplication: false,
    },
    routes: {
      show: '/ideas/collab-brief',
      edit: '/ideas/collab-brief/edit',
      dashboard: '/dashboard',
      update: '/ideas/collab-brief',
      applicationsCreate: '/ideas/collab-brief/applications/create',
      applicationsStore: '/ideas/collab-brief/applications',
      commentsStore: '/ideas/collab-brief/comments',
      supportersStore: '/ideas/collab-brief/supporters',
      repositoryCreate: '/ideas/collab-brief/repository-create',
      repositoryInvite: '/ideas/collab-brief/repository-invite',
    },
    ...overrides,
  };
}

describe('IdeaSidebar', () => {
  it('renders repository activity as a collapsible timeline', async () => {
    const wrapper = mount(IdeaSidebar, {
      props: {
        idea: idea({
          repositoryActivity: repositoryActivity({
            events: [
              repositoryEvent(1, 'repository_commit', 'Latest commit: Tighten onboarding flow'),
              repositoryEvent(2, 'repository_synced', 'Repository sync connected to GitHub.'),
              repositoryEvent(3, 'repository_issues_changed', 'Open issues changed from 3 to 4.'),
              repositoryEvent(4, 'repository_branch_changed', 'Default branch changed from master to main.'),
            ],
          }),
        }),
      },
    });

    expect(wrapper.get('a[href="https://github.com/example/collab-brief"]').text()).toContain('Open repository on GitHub');
    expect(wrapper.text()).toContain('Latest commit');
    expect(wrapper.text()).toContain('Tighten onboarding flow');
    expect(wrapper.text()).toContain('octocat - abc1234');

    const timeline = wrapper.get('ol[aria-label="Repository activity timeline"]');

    expect(timeline.element.tagName).toBe('OL');
    expect(timeline.findAll('li')).toHaveLength(3);

    const button = wrapper.get('button[aria-controls="repository-older-events-42"]');

    expect(button.attributes('aria-expanded')).toBe('false');
    expect(wrapper.get('#repository-older-events-42').attributes('style')).toContain('display: none');

    await button.trigger('click');

    expect(button.attributes('aria-expanded')).toBe('true');
    expect(wrapper.get('#repository-older-events-42').attributes('style')).toBe('');
    expect(wrapper.get('#repository-older-events-42').text()).toContain('Default branch changed from master to main.');
  });

  it('renders the missing repository state clearly', () => {
    const wrapper = mount(IdeaSidebar, {
      props: {
        idea: idea({
          repository: false,
          repositoryActivity: repositoryActivity({
            htmlUrl: null,
            isMissing: true,
            latestCommitMessage: null,
            latestCommitAuthor: null,
            latestCommitShortSha: null,
            events: [repositoryEvent(1, 'repository_missing', 'Repository is no longer available on GitHub.')],
          }),
        }),
      },
    });

    expect(wrapper.text()).toContain('Missing');
    expect(wrapper.text()).toContain('Repository unavailable');
    expect(wrapper.text()).toContain('GitHub no longer reports this repository as available.');
    expect(wrapper.find('a[href*="github.com"]').exists()).toBe(false);
  });

  it('uses sync state when no latest commit is available', () => {
    const wrapper = mount(IdeaSidebar, {
      props: {
        idea: idea({
          repositoryActivity: repositoryActivity({
            latestCommitSha: null,
            latestCommitShortSha: null,
            latestCommitMessage: null,
            latestCommitAuthor: null,
            events: [],
          }),
        }),
      },
    });

    expect(wrapper.text()).toContain('Synced');
    expect(wrapper.text()).toContain('Repository sync is current');
    expect(wrapper.text()).toContain('No repository events have been recorded yet.');
  });
});
