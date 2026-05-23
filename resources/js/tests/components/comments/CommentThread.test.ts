import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import CommentThread from '@/components/comments/CommentThread.vue';
import type { DomainUser, IdeaComment } from '@/types/domain';

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

function comment(): IdeaComment {
  return {
    id: 1,
    parentId: null,
    content: 'Original comment',
    contentHtml: '<p>Original comment</p>',
    createdAtForHumans: '1 minute ago',
    updatedAtForHumans: '1 minute ago',
    wasEdited: false,
    user: user(),
    replies: [],
    can: {
      update: true,
    },
    routes: {
      edit: '/ideas/1/comments/1/edit',
      update: '/ideas/1/comments/1',
    },
  };
}

function mountThread() {
  return mount(CommentThread, {
    props: {
      comment: comment(),
      commentsStore: '/ideas/1/comments',
      mentionableUsers: [user()],
    },
    global: {
      stubs: {
        CommentComposer: {
          emits: [
            'cancel',
            'submitted',
          ],
          props: ['buttonLabel'],
          template: '<form data-testid="comment-composer" @submit.prevent="$emit(\'submitted\')"><button type="submit">{{ buttonLabel }}</button></form>',
        },
        MarkdownContent: {
          props: ['html'],
          template: '<div data-testid="markdown-content" v-html="html" />',
        },
      },
    },
  });
}

describe('CommentThread', () => {
  it('closes edit mode after saving changes', async () => {
    const wrapper = mountThread();
    const editButton = wrapper.findAll('button').find((button) => button.text() === 'Edit');

    expect(editButton).toBeDefined();

    await editButton?.trigger('click');

    expect(wrapper.find('[data-testid="comment-composer"]').exists()).toBe(true);

    await wrapper.get('[data-testid="comment-composer"]').trigger('submit');

    expect(wrapper.find('[data-testid="comment-composer"]').exists()).toBe(false);
    expect(wrapper.find('[data-testid="markdown-content"]').exists()).toBe(true);
  });
});
