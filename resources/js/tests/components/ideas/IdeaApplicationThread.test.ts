import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import IdeaApplicationThread from '@/components/ideas/IdeaApplicationThread.vue';
import type { DomainUser, IdeaApplication } from '@/types/domain';

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

function application(overrides: Partial<IdeaApplication> = {}): IdeaApplication {
  return {
    id: 3,
    content: 'Application context.',
    contentHtml: '<p>Application context.</p>',
    contributionType: 'testing',
    contributionTypeDisplay: 'Testing',
    firstAction: 'Write a regression test.',
    approvalNote: null,
    approvalNoteHtml: null,
    declineReason: null,
    status: 'pending',
    statusDisplay: 'Pending',
    createdAtForHumans: 'Today',
    user: user(),
    thread: {
      messages: [{
        id: 1,
        type: 'message',
        body: 'Can I start with **tests**?',
        bodyHtml: '<p>Can I start with <strong>tests</strong>?</p>',
        isSystem: false,
        occurredAtForHumans: '1 minute ago',
        user: user({
          name: 'Applied User',
        }),
      }],
      unreadCount: 0,
      hasUnread: false,
      canMessage: true,
      isReadOnly: false,
      readOnlyReason: null,
      routes: {
        read: '/applications/3/read-state',
        store: '/applications/3/messages',
      },
    },
    routes: {
      destroy: '/applications/3',
      edit: '/applications/3/edit',
      update: '/applications/3',
      approve: '/applications/3/approve',
    },
    ...overrides,
  };
}

function mountThread(app: IdeaApplication = application()) {
  return mount(IdeaApplicationThread, {
    props: {
      application: app,
    },
    global: {
      stubs: {
        CsrfField: true,
      },
    },
  });
}

describe('IdeaApplicationThread', () => {
  it('renders private markdown messages and a composer', () => {
    const wrapper = mountThread();

    expect(wrapper.text()).toContain('Private application thread');
    expect(wrapper.text()).toContain('Visible only to the idea owner and applicant.');
    expect(wrapper.html()).toContain('<strong>tests</strong>');
    expect(wrapper.get('form').attributes('action')).toBe('/applications/3/messages');
    expect(wrapper.get('textarea[name="body"]').attributes('maxlength')).toBe('1500');
  });

  it('renders system messages with friendly labels', () => {
    const wrapper = mountThread(application({
      thread: {
        ...application().thread!,
        messages: [
          {
            id: 2,
            type: 'withdrawn',
            body: null,
            bodyHtml: null,
            isSystem: true,
            occurredAtForHumans: 'just now',
            user: null,
          },
          {
            id: 3,
            type: 'left',
            body: 'Stepping away from this work.',
            bodyHtml: '<p>Stepping away from this work.</p>',
            isSystem: true,
            occurredAtForHumans: 'just now',
            user: null,
          },
          {
            id: 4,
            type: 'removed',
            body: null,
            bodyHtml: null,
            isSystem: true,
            occurredAtForHumans: 'just now',
            user: null,
          },
        ],
      },
    }));

    expect(wrapper.text()).toContain('Application withdrawn');
    expect(wrapper.text()).toContain('Collaborator left');
    expect(wrapper.text()).toContain('Stepping away from this work.');
    expect(wrapper.text()).toContain('Collaborator removed');
    expect(wrapper.text()).toContain('just now');
  });

  it('marks unread threads as read after render', async () => {
    const fetch = vi.fn().mockResolvedValue({
      ok: true,
    });
    vi.stubGlobal('fetch', fetch);

    const wrapper = mountThread(application({
      thread: {
        ...application().thread!,
        hasUnread: true,
        unreadCount: 2,
      },
    }));

    expect(wrapper.text()).toContain('2 new');

    await vi.waitFor(() => {
      expect(fetch).toHaveBeenCalledWith('/applications/3/read-state', expect.objectContaining({
        method: 'PUT',
      }));
      expect(wrapper.text()).not.toContain('2 new');
    });

    vi.unstubAllGlobals();
  });

  it('renders final states as read-only', () => {
    const wrapper = mountThread(application({
      thread: {
        ...application().thread!,
        canMessage: false,
        isReadOnly: true,
        readOnlyReason: 'This application thread is read-only after a final decision.',
      },
    }));

    expect(wrapper.text()).toContain('Read-only');
    expect(wrapper.text()).toContain('This application thread is read-only after a final decision.');
    expect(wrapper.find('form').exists()).toBe(false);
  });
});
