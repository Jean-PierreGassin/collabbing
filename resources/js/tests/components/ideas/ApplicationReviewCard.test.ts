import { mount } from '@vue/test-utils';
import { createPinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import ApplicationReviewCard from '@/components/ideas/ApplicationReviewCard.vue';
import type { DomainUser, IdeaApplication } from '@/types/domain';

vi.mock('@inertiajs/vue3', () => ({
  usePage: () => ({
    props: {
      auth: { user: null },
      errors: {},
      flash: {},
      oldInput: {},
      routes: {},
    },
  }),
}));

function applicant(overrides: Partial<DomainUser> = {}): DomainUser {
  return {
    bio: null,
    bioHtml: null,
    canUpdate: false,
    createdAtFormatted: 'May 12, 2026',
    email: 'ada@example.test',
    firstName: 'Ada',
    githubUsername: 'ada-labs',
    hasGithubToken: true,
    id: 9,
    lastName: 'Lovelace',
    name: 'Ada Lovelace',
    profilePicture: '/avatars/ada.png',
    routes: {
      edit: '/users/ada/edit',
      githubLogin: '/github/login',
      githubRevoke: '/github/revoke',
      show: '/users/ada',
      update: '/users/ada',
    },
    username: 'ada',
    ...overrides,
  };
}

function application(overrides: Partial<IdeaApplication> = {}): IdeaApplication {
  return {
    content: 'I can build the prototype and write the docs.',
    contentHtml: '<p>I can build the prototype and write the docs.</p>',
    createdAtForHumans: '2 hours ago',
    id: 7,
    routes: {
      approve: '/ideas/42/applications/7/approve',
      destroy: '/ideas/42/applications/7',
    },
    status: 'pending',
    user: applicant(),
    ...overrides,
  };
}

beforeEach(() => {
  const token = document.createElement('meta');
  token.name = 'csrf-token';
  token.content = 'test-csrf-token';
  document.head.append(token);
});

describe('ApplicationReviewCard', () => {
  it('shows applicant identity, submitted time, and expandable content', async () => {
    const wrapper = mount(ApplicationReviewCard, {
      global: {
        plugins: [createPinia()],
      },
      props: {
        application: application(),
        canDelete: true,
        canUpdate: true,
      },
    });

    expect(wrapper.text()).toContain('Ada Lovelace');
    expect(wrapper.text()).toContain('@ada');
    expect(wrapper.text()).toContain('GitHub: ada-labs');
    expect(wrapper.text()).toContain('Submitted 2 hours ago');

    const content = wrapper.get('#application-content-7');
    expect(content.classes()).toContain('max-h-40');
    expect(content.classes()).toContain('overflow-hidden');

    const toggle = wrapper.get('button[aria-controls="application-content-7"]');
    expect(toggle.attributes('aria-expanded')).toBe('false');

    await toggle.trigger('click');

    expect(wrapper.get('#application-content-7').classes()).toContain('max-h-none');
    expect(wrapper.get('button[aria-controls="application-content-7"]').attributes('aria-expanded')).toBe('true');
    expect(wrapper.text()).toContain('Show less');
  });

  it('keeps review actions deliberate and submits with the expected methods', async () => {
    const wrapper = mount(ApplicationReviewCard, {
      global: {
        plugins: [createPinia()],
      },
      props: {
        application: application(),
        canDelete: true,
        canUpdate: true,
      },
    });

    const declineForm = wrapper.get('form[action="/ideas/42/applications/7"]');
    expect(declineForm.get('input[name="_token"]').attributes('value')).toBe('test-csrf-token');
    expect(declineForm.get('input[name="_method"]').attributes('value')).toBe('DELETE');

    await declineForm.get('button').trigger('click');

    expect(wrapper.text()).toContain('Declining permanently removes this application.');
    expect(wrapper.text()).toContain('Confirm decline');
    expect(declineForm.get('input[name="_method"]').attributes('value')).toBe('DELETE');

    const approveForm = wrapper.get('form[action="/ideas/42/applications/7/approve"]');
    expect(approveForm.get('input[name="_token"]').attributes('value')).toBe('test-csrf-token');
    expect(approveForm.get('input[name="_method"]').attributes('value')).toBe('PUT');
  });
});
