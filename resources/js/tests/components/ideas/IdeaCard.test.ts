import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import IdeaCard from '@/components/ideas/IdeaCard.vue';
import type { DomainUser, Idea, IdeaCollaboration } from '@/types/domain';

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
    tags: [
      'design-systems',
      'workflow',
    ],
    communication: 'Slack',
    collaboration: collaboration(),
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

function mountCard(variant: 'compact' | 'detailed', overrides: Partial<Idea> = {}) {
  return mount(IdeaCard, {
    props: {
      idea: idea(overrides),
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

function mountSingle(overrides: Partial<Idea> = {}) {
  return mount(IdeaCard, {
    props: {
      idea: idea(overrides),
      single: true,
    },
    global: {
      stubs: {
        Link: {
          props: ['href'],
          template: '<a :href="href"><slot /></a>',
        },
        MarkdownContent: {
          props: ['html'],
          template: '<div v-html="html" />',
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

  it('keeps collaboration badges off compact cards', () => {
    const wrapper = mountCard('compact', {
      collaboration: collaboration({
        stage: 'ready_to_build',
        stageDisplay: 'Ready to build',
        helpWanted: [
          'frontend',
          'backend',
          'testing',
          'design',
        ],
        helpWantedDisplay: [
          'Frontend',
          'Backend',
          'Testing',
          'Design',
        ],
        firstContribution: 'Review the first issue.',
        applicationsOpen: false,
      }),
    });
    const text = wrapper.text();

    expect(text).not.toContain('Collaboration');
    expect(text).not.toContain('Ready to build');
    expect(text).not.toContain('Frontend');
    expect(text).not.toContain('Backend');
    expect(text).not.toContain('Testing');
    expect(text).not.toContain('Design');
    expect(text).not.toContain('Applications closed');
    expect(text).not.toContain('First step listed');
    expect(text).not.toContain('Stage:');
    expect(text).not.toContain('Needs:');
    expect(text).not.toContain('Review the first issue.');
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

  it('keeps collaboration badges off detailed cards', () => {
    const wrapper = mountCard('detailed', {
      collaboration: collaboration({
        stage: 'actively_building',
        stageDisplay: 'Actively building',
        helpWanted: [
          'product',
          'research',
        ],
        helpWantedDisplay: [
          'Product',
          'Research',
        ],
        firstContribution: 'Map the onboarding state.',
        applicationsOpen: true,
      }),
    });
    const text = wrapper.text();

    expect(text).not.toContain('Collaboration');
    expect(text).not.toContain('Actively building');
    expect(text).not.toContain('Product');
    expect(text).not.toContain('Research');
    expect(text).not.toContain('First step listed');
    expect(text).not.toContain('Stage:');
    expect(text).not.toContain('Needs:');
    expect(text).not.toContain('Applications closed');
    expect(text).not.toContain('Map the onboarding state.');
  });

  it('shows application status instead of generic idea status on the full card', () => {
    const wrapper = mountSingle({
      collaboration: collaboration({
        helpWantedDisplay: ['Frontend'],
      }),
      status: 'open',
      statusDisplay: 'Open',
    });

    expect(wrapper.get('[aria-label="Application status: Applications open"]').text()).toBe('Applications open');
    expect(wrapper.text()).not.toContain('Status');
  });

  it('formats compact project context above the full card summary', () => {
    const wrapper = mountSingle({
      collaboration: collaboration({
        stage: 'ready_to_build',
        stageDisplay: 'Ready to build',
        helpWantedDisplay: [
          'Frontend',
          'Backend',
          'Testing',
        ],
        firstContribution: 'Review the first issue.',
        applicationsOpen: false,
      }),
    });
    const context = wrapper.get('[aria-label="Project context"]').text();
    const summary = wrapper.get('[aria-label="Idea summary"]').text();
    const fullText = wrapper.text();

    expect(wrapper.get('[aria-label="Application status: Applications closed"]').text()).toBe('Applications closed');
    expect(fullText.indexOf('Project context')).toBeLessThan(fullText.indexOf('Summary'));
    expect(context).toContain('Stage');
    expect(context).toContain('Ready to build');
    expect(context).toContain('Help');
    expect(context).toContain('Frontend, Backend +1');
    expect(context).not.toContain('First useful step');
    expect(context).not.toContain('Repository');
    expect(summary).toContain('A summary that belongs on the full idea page.');
    expect(wrapper.text()).not.toContain('Review the first issue.');
  });

  it('keeps card body layers pass-through so cards remain clickable', () => {
    const compact = mountCard('compact');
    const detailed = mountCard('detailed');

    expect(compact.get('a[aria-label="Open Useful idea"]').attributes('href')).toBe('/ideas/1');
    expect(detailed.get('a[aria-label="Open Useful idea"]').attributes('href')).toBe('/ideas/1');
    expect(compact.findAll('a[href="/ideas/1"]')).toHaveLength(1);
    expect(detailed.findAll('a[href="/ideas/1"]')).toHaveLength(1);
  });

  it('shows the manage action once at the top of compact and detailed cards', () => {
    const compact = mountCard('compact', {
      can: {
        ...idea().can,
        update: true,
      },
    });
    const detailed = mountCard('detailed', {
      can: {
        ...idea().can,
        update: true,
      },
    });

    expect(compact.findAll('a[href="/ideas/1/dashboard"]')).toHaveLength(1);
    expect(detailed.findAll('a[href="/ideas/1/dashboard"]')).toHaveLength(1);
    expect(compact.get('a[href="/ideas/1/dashboard"]').text()).toContain('Manage');
    expect(detailed.get('a[href="/ideas/1/dashboard"]').text()).toContain('Manage');
  });

  it('shows pending application counts only to owners', () => {
    const publicCard = mountCard('compact', {
      pendingApplicationsCount: null,
    });
    const ownerCard = mountCard('compact', {
      pendingApplicationsCount: 2,
      can: {
        ...idea().can,
        update: true,
      },
    });

    expect(publicCard.text()).not.toContain('pending');
    expect(ownerCard.text()).toContain('2 pending applications');
  });
});
