import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import { describe, expect, it, vi } from 'vitest';
import CommentComposer from '@/components/comments/CommentComposer.vue';
import type { DomainUser } from '@/types/domain';

const session = vi.hoisted(() => ({
  csrfToken: 'test-token',
  errors: {} as Record<string, string>,
  oldInput: {} as Record<string, string>,
}));

vi.mock('@/stores/session', () => ({
  useSessionStore: () => session,
}));

function user(id: number, username: string, name = username): DomainUser {
  return {
    id,
    username,
    firstName: name,
    lastName: 'User',
    name,
    email: null,
    bio: null,
    bioHtml: null,
    githubUsername: null,
    hasGithubToken: false,
    profilePicture: '',
    createdAtFormatted: '',
    canUpdate: false,
    routes: {
      show: `/users/${username}`,
      edit: `/users/${username}/edit`,
      update: `/users/${username}`,
      githubLogin: `/users/${username}/github`,
      githubRevoke: `/users/${username}/github/revoke`,
    },
  };
}

function mountComposer(mentionableUsers: DomainUser[]) {
  return mount(CommentComposer, {
    props: {
      action: '/comments',
      mentionableUsers,
      textareaId: 'comment-content',
    },
  });
}

async function typeComment(wrapper: ReturnType<typeof mountComposer>, value: string, cursor = value.length): Promise<void> {
  const textarea = wrapper.get('textarea');
  const element = textarea.element as HTMLTextAreaElement;

  element.value = value;
  element.setSelectionRange(cursor, cursor);

  await textarea.trigger('input');
  await nextTick();
}

describe('CommentComposer', () => {
  it('exposes mention suggestions as an accessible combobox listbox', async () => {
    const wrapper = mountComposer([
      user(1, 'ava', 'Ava Stone'),
      user(2, 'avery', 'Avery Park'),
      user(3, 'avi', 'Avi Shah'),
      user(4, 'axel', 'Axel Moore'),
      user(5, 'azra', 'Azra Hall'),
      user(6, 'apollo', 'Apollo Lane'),
    ]);

    await typeComment(wrapper, '@a');

    const textarea = wrapper.get('textarea');
    const options = wrapper.findAll('[role="option"]');

    expect(textarea.attributes('role')).toBe('combobox');
    expect(textarea.attributes('aria-autocomplete')).toBe('list');
    expect(textarea.attributes('aria-expanded')).toBe('true');
    expect(textarea.attributes('aria-controls')).toBe('comment-content-mention-suggestions');
    expect(textarea.attributes('aria-activedescendant')).toBe('comment-content-mention-suggestions-option-1');
    expect(wrapper.get('#comment-content-mention-suggestions').attributes('role')).toBe('listbox');
    expect(options).toHaveLength(5);
    expect(options.map((option) => option.find('.font-medium').text())).toEqual([
      '@ava',
      '@avery',
      '@avi',
      '@axel',
      '@azra',
    ]);
    expect(options.map((option) => option.find('.text-muted-foreground').text())).toEqual([
      'Ava Stone',
      'Avery Park',
      'Avi Shah',
      'Axel Moore',
      'Azra Hall',
    ]);
  });

  it('moves through suggestions with arrows and inserts the active mention with enter', async () => {
    const wrapper = mountComposer([
      user(1, 'ava', 'Ava Stone'),
      user(2, 'avery', 'Avery Park'),
      user(3, 'avi', 'Avi Shah'),
    ]);

    await typeComment(wrapper, 'Thanks @av for pairing', 'Thanks @av'.length);

    await wrapper.get('textarea').trigger('keydown', { key: 'ArrowDown' });

    expect(wrapper.get('textarea').attributes('aria-activedescendant')).toBe('comment-content-mention-suggestions-option-2');
    expect(wrapper.findAll('[role="option"]')[1].attributes('aria-selected')).toBe('true');

    await wrapper.get('textarea').trigger('keydown', { key: 'Enter' });
    await nextTick();

    const textarea = wrapper.get('textarea').element as HTMLTextAreaElement;

    expect(textarea.value).toBe('Thanks @avery for pairing');
    expect(textarea.selectionStart).toBe('Thanks @avery '.length);
  });

  it('supports tab insertion, escape dismissal, and reopening after the query changes', async () => {
    const wrapper = mountComposer([
      user(1, 'ava', 'Ava Stone'),
      user(2, 'avery', 'Avery Park'),
    ]);

    await typeComment(wrapper, '@av');
    await wrapper.get('textarea').trigger('keydown', { key: 'Escape' });

    expect(wrapper.get('textarea').attributes('aria-expanded')).toBe('false');
    expect(wrapper.find('[role="listbox"]').exists()).toBe(false);

    await typeComment(wrapper, '@ave');

    expect(wrapper.get('textarea').attributes('aria-expanded')).toBe('true');

    await wrapper.get('textarea').trigger('keydown', { key: 'Tab' });
    await nextTick();

    expect((wrapper.get('textarea').element as HTMLTextAreaElement).value).toBe('@avery ');
  });

  it('inserts suggestions with pointer input', async () => {
    const wrapper = mountComposer([
      user(1, 'ava', 'Ava Stone'),
      user(2, 'avery', 'Avery Park'),
    ]);

    await typeComment(wrapper, 'Loop in @av', 'Loop in @av'.length);
    await wrapper.findAll('[role="option"]')[1].trigger('pointerdown');
    await nextTick();

    const textarea = wrapper.get('textarea').element as HTMLTextAreaElement;

    expect(textarea.value).toBe('Loop in @avery ');
    expect(textarea.selectionStart).toBe('Loop in @avery '.length);
  });
});
