import { mount } from '@vue/test-utils';
import { afterEach, describe, expect, it, vi } from 'vitest';
import UsersIndex from '@/pages/Users/Index.vue';
import type { DomainUser, Paginator } from '@/types/domain';

function member(overrides: Partial<DomainUser> = {}): DomainUser {
  const id = overrides.id ?? 1;
  const username = overrides.username ?? 'jane';
  const name = overrides.name ?? 'Jane Doe';

  return {
    id,
    username,
    firstName: 'Jane',
    lastName: 'Doe',
    name,
    email: null,
    bio: 'Builds careful collaboration tools for distributed product teams.',
    bioHtml: null,
    githubUsername: 'janedoe',
    hasGithubToken: true,
    profilePicture: `/avatars/${username}.png`,
    createdAtFormatted: 'May 2026',
    canUpdate: false,
    routes: {
      show: `/members/${username}`,
      edit: `/members/${username}/edit`,
      update: `/members/${username}`,
      githubLogin: '/settings/github',
      githubRevoke: '/settings/github/revoke',
    },
    ...overrides,
  };
}

function paginator(items: DomainUser[]): Paginator<DomainUser> {
  return {
    currentPage: 1,
    items,
    lastPage: 1,
    nextPageUrl: null,
    previousPageUrl: null,
  };
}

function mountPage(items: DomainUser[]) {
  return mount(UsersIndex, {
    props: {
      users: paginator(items),
    },
    global: {
      stubs: {
        PaginationLinks: true,
      },
    },
  });
}

function anchorWithText(wrapper: ReturnType<typeof mountPage>, href: string, text: string) {
  const anchor = wrapper.findAll(`a[href="${href}"]`).find((link) => link.text().includes(text));

  if (! anchor) {
    throw new Error(`Expected to find an anchor linking to ${href} with text ${text}.`);
  }

  return anchor;
}

afterEach(() => {
  vi.useRealTimers();
  vi.restoreAllMocks();
});

describe('UsersIndex', () => {
  it('renders profile and GitHub links as real anchors', () => {
    const wrapper = mountPage([
      member(),
      member({
        id: 2,
        username: 'alex',
        name: 'Alex Smith',
        githubUsername: null,
        hasGithubToken: false,
      }),
    ]);

    expect(anchorWithText(wrapper, '/members/jane', 'Jane Doe').exists()).toBe(true);
    expect(anchorWithText(wrapper, '/members/jane', 'View profile').exists()).toBe(true);
    expect(wrapper.get('a[href="https://github.com/janedoe"]').attributes('target')).toBe('_blank');
    expect(wrapper.text()).toContain('GitHub connected');
    expect(wrapper.text()).toContain('GitHub not listed');
  });

  it('expands and collapses long bios without hiding the profile action', async () => {
    const wrapper = mountPage([
      member({
        bio: 'This member works across product discovery, implementation planning, release coordination, and community support for collaboration-heavy projects that benefit from thoughtful contributor context.',
      }),
    ]);

    const bio = wrapper.get('p.break-words');
    const toggle = wrapper.get('button[aria-expanded="false"]');

    expect(bio.classes()).toContain('line-clamp-3');
    expect(toggle.text()).toContain('Show bio');
    expect(anchorWithText(wrapper, '/members/jane', 'View profile').exists()).toBe(true);

    await toggle.trigger('click');

    expect(wrapper.get('button[aria-expanded="true"]').text()).toContain('Hide bio');
    expect(wrapper.get('p.break-words').classes()).not.toContain('line-clamp-3');

    await wrapper.get('button[aria-expanded="true"]').trigger('click');

    expect(wrapper.get('button[aria-expanded="false"]').text()).toContain('Show bio');
  });

  it('copies the absolute profile link and announces copied feedback', async () => {
    vi.useFakeTimers();

    const writeText = vi.fn().mockResolvedValue(undefined);

    Object.defineProperty(navigator, 'clipboard', {
      configurable: true,
      value: {
        writeText,
      },
    });

    const wrapper = mountPage([member()]);
    const copyButton = wrapper.get('button[aria-label="Copy Jane Doe profile link"]');

    await copyButton.trigger('click');
    await Promise.resolve();

    expect(writeText).toHaveBeenCalledWith(new URL('/members/jane', window.location.origin).toString());
    expect(wrapper.get('button[aria-label="Copy Jane Doe profile link"]').text()).toContain('Copied');
    expect(wrapper.text()).toContain('Profile link copied.');

    vi.advanceTimersByTime(2200);
    await wrapper.vm.$nextTick();

    expect(wrapper.get('button[aria-label="Copy Jane Doe profile link"]').text()).toContain('Copy link');
  });
});
