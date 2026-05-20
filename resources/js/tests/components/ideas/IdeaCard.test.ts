import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import IdeaCard from '@/components/ideas/IdeaCard.vue';
import type { DomainUser, Idea } from '@/types/domain';

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
    tagline: 'A concise product tagline.',
    summary: 'A summary that belongs on the full idea page.',
    tags: ['design-systems', 'workflow'],
    communication: 'Slack',
    content: 'A focused pitch.',
    contentHtml: '<p>A focused pitch.</p>',
    status: 'open',
    statusDisplay: 'Open',
    repository: true,
    repositoryName: 'useful-idea',
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
    supportersCount: 42,
    approvedApplicationsCount: 7,
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

function mountCard(variant: 'compact' | 'detailed') {
  return mount(IdeaCard, {
    props: {
      idea: idea(),
      variant,
    },
    global: {
      stubs: {
        Link: {
          props: ['href'],
          template: '<a :href="href"><slot /></a>',
        },
      },
    },
  });
}

function occurrences(text: string, needle: string): number {
  return text.split(needle).length - 1;
}

describe('IdeaCard', () => {
  it('renders compact card details once', () => {
    const wrapper = mountCard('compact');
    const text = wrapper.text();

    expect(occurrences(text, 'A concise product tagline.')).toBe(1);
    expect(occurrences(text, 'design-systems')).toBe(1);
    expect(occurrences(text, '42 supporters')).toBe(1);
    expect(occurrences(text, '7 collaborators')).toBe(1);
    expect(text).not.toContain('Repository');
  });

  it('renders detailed cards as slim rows without summary or labels', () => {
    const wrapper = mountCard('detailed');
    const text = wrapper.text();

    expect(occurrences(text, 'A concise product tagline.')).toBe(1);
    expect(occurrences(text, 'design-systems')).toBe(1);
    expect(text).not.toContain('A summary that belongs on the full idea page.');
    expect(text).not.toContain('Tagline');
    expect(text).not.toContain('Repository');
    expect(text).not.toContain('Copy link');
    expect(text).toContain('42');
    expect(text).toContain('supporters');
    expect(text).toContain('7');
    expect(text).toContain('collaborators');
    expect(wrapper.findAll('a[href="/ideas/1"]')).toHaveLength(1);
  });

  it('keeps card body layers pass-through so cards remain clickable', () => {
    const compact = mountCard('compact');
    const detailed = mountCard('detailed');

    expect(compact.find('.pointer-events-none.relative.z-10').exists()).toBe(true);
    expect(detailed.find('.pointer-events-none.relative.z-10').exists()).toBe(true);
    expect(compact.get('a[aria-label="Open Useful idea"]').attributes('href')).toBe('/ideas/1');
    expect(detailed.get('a[aria-label="Open Useful idea"]').attributes('href')).toBe('/ideas/1');
  });
});
