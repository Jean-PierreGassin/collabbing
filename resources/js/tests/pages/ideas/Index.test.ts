import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it } from 'vitest';
import IdeasIndex from '@/pages/Ideas/Index.vue';
import type { DomainUser, Idea, IdeaCollaboration, IdeaTag, Paginator } from '@/types/domain';

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
    tags: [
      'design',
      'workflow',
    ],
    communication: 'Slack',
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
  selectedTag?: string | null;
  popularTags?: IdeaTag[];
  searchResults?: Paginator<Idea> | null;
  trendingIdeas?: Idea[];
  ideas?: Paginator<Idea>;
}) {
  return mount(IdeasIndex, {
    props: {
      keyword: null,
      selectedTag: null,
      popularTags: [],
      searchResults: null,
      trendingIdeas: [],
      ideas: paginator([]),
      ...props,
    },
    global: {
      stubs: {
        IdeaList: {
          props: [
            'ideas',
            'featured',
            'variant',
          ],
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
  beforeEach(() => {
    window.localStorage.clear();
  });

  it('keeps trending ideas out of the recent list', () => {
    const trending = idea({ id: 1, title: 'Trending idea' });
    const recent = idea({ id: 2, title: 'Recent idea' });
    const wrapper = mountIndex({
      trendingIdeas: [trending],
      ideas: paginator([
        trending,
        recent,
      ]),
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
    expect(window.localStorage.getItem('collabbing.ideaViewMode')).toBe('detailed');
  });

  it('restores the users card density preference', () => {
    window.localStorage.setItem('collabbing.ideaViewMode', 'detailed');

    const wrapper = mountIndex({
      trendingIdeas: [idea({ id: 1 })],
      ideas: paginator([idea({ id: 2 })]),
    });

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

  it('renders popular tags as filter links', () => {
    const wrapper = mountIndex({
      keyword: 'review',
      popularTags: [
        { name: 'design', count: 3 },
        { name: 'workflow', count: 1 },
      ],
    });

    const tagLinks = wrapper
      .findAll('a')
      .filter((link) => link.attributes('href')?.includes('tag='));

    expect(wrapper.text()).toContain('Browse by tag');
    expect(tagLinks[0].attributes('href')).toBe('/ideas?search=review&tag=design');
    expect(tagLinks[0].text()).toContain('design');
    expect(tagLinks[0].text()).toContain('3');
    expect(tagLinks[1].attributes('href')).toBe('/ideas?search=review&tag=workflow');
    expect(tagLinks[1].text()).toContain('workflow');
  });

  it('shows active tag results and keeps the search when clearing the tag', () => {
    const wrapper = mountIndex({
      keyword: 'review',
      selectedTag: 'design',
      popularTags: [{ name: 'design', count: 3 }],
      searchResults: paginator([idea({ id: 3 })]),
    });

    expect(wrapper.text()).toContain('Results for "review" tagged design');
    expect(wrapper.text()).toContain('Open ideas matching your search and selected tag');
    expect(wrapper.get('a[aria-current="true"]').text()).toContain('design');
    expect(wrapper.get('a[href="/ideas?search=review"]').text()).toContain('Clear tag');
    expect(wrapper.find('[data-list]')?.text()).toBe('3');
  });
});
