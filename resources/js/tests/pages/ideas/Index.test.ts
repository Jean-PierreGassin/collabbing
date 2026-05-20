import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import IdeasIndex from '@/pages/Ideas/Index.vue';
import type { DomainUser, Idea, Paginator } from '@/types/domain';

function user(): DomainUser {
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
  };
}

function idea(overrides: Partial<Idea> = {}): Idea {
  return {
    id: 1,
    title: 'Useful idea',
    titleDisplay: 'Useful idea',
    tagline: 'A useful card tagline.',
    summary: 'A useful idea for collaborators.',
    tags: ['design', 'workflow'],
    communication: 'Slack',
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

function paginator(items: Idea[]): Paginator<Idea> {
  return {
    items,
    currentPage: 1,
    lastPage: 1,
    previousPageUrl: null,
    nextPageUrl: null,
  };
}

function mountIndex(props: {
  keyword?: string | null;
  searchResults?: Paginator<Idea> | null;
  trendingIdeas?: Idea[];
  ideas?: Paginator<Idea>;
}) {
  return mount(IdeasIndex, {
    props: {
      keyword: null,
      searchResults: null,
      trendingIdeas: [],
      ideas: paginator([]),
      ...props,
    },
    global: {
      stubs: {
        IdeaList: {
          props: ['ideas', 'featured', 'variant'],
          template: '<div data-list :data-featured="featured ? \'true\' : \'false\'" :data-variant="variant">{{ ideas.map((idea) => idea.id).join(\',\') }}</div>',
        },
        PaginationLinks: {
          props: ['paginator'],
          template: '<nav v-if="paginator.lastPage > 1">Pagination</nav>',
        },
      },
    },
  });
}

describe('Ideas/Index', () => {
  it('keeps trending ideas out of the recent list', () => {
    const trending = idea({ id: 1, title: 'Trending idea' });
    const recent = idea({ id: 2, title: 'Recent idea' });
    const wrapper = mountIndex({
      trendingIdeas: [trending],
      ideas: paginator([trending, recent]),
    });

    const lists = wrapper.findAll('[data-list]');

    expect(lists).toHaveLength(2);
    expect(lists[0].text()).toBe('1');
    expect(lists[1].text()).toBe('2');
  });

  it('lets users switch discovery cards into detailed mode', async () => {
    const wrapper = mountIndex({
      trendingIdeas: [idea({ id: 1 })],
      ideas: paginator([idea({ id: 2 })]),
    });

    await wrapper.get('button[aria-pressed="false"]').trigger('click');

    expect(wrapper.findAll('[data-list]').every((list) => list.attributes('data-variant') === 'detailed')).toBe(true);
    expect(wrapper.get('button[aria-pressed="true"]').text()).toContain('Detailed');
  });

  it('gives empty search results clear recovery actions', () => {
    const wrapper = mountIndex({
      keyword: 'nothing',
      searchResults: paginator([]),
    });

    expect(wrapper.text()).toContain('No ideas matched "nothing".');
    expect(wrapper.get('a[href="/ideas"]').text()).toContain('Clear search');
    expect(wrapper.get('a[href="/ideas/create"]').text()).toContain('Share an idea');
  });
});
