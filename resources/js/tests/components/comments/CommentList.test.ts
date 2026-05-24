import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import CommentList from '@/components/comments/CommentList.vue';
import type { DomainUser, IdeaComment, Paginator } from '@/types/domain';

function paginator(items: IdeaComment[] = []): Paginator<IdeaComment> {
  return {
    currentPage: 1,
    items,
    lastPage: 1,
    nextPageUrl: null,
    previousPageUrl: null,
  };
}

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
    content: 'Public comment',
    contentHtml: '<p>Public comment</p>',
    createdAtForHumans: 'today',
    updatedAtForHumans: 'today',
    wasEdited: false,
    user: user(),
    replies: [],
    can: {
      update: false,
    },
    routes: {
      edit: '/ideas/1/comments/1/edit',
      update: '/ideas/1/comments/1',
    },
  };
}

function mountList(canStoreComment: boolean) {
  return mount(CommentList, {
    props: {
      canStoreComment,
      comments: paginator([comment()]),
      commentsStore: '/ideas/1/comments',
      mentionableUsers: [user()],
    },
    global: {
      stubs: {
        CommentComposer: {
          template: '<form data-testid="comment-composer"></form>',
        },
        CommentThread: {
          props: ['comment'],
          template: '<article data-testid="comment-thread">{{ comment.content }}</article>',
        },
        PaginationLinks: true,
      },
    },
  });
}

describe('CommentList', () => {
  it('shows public comments without the composer when commenting is unavailable', () => {
    const wrapper = mountList(false);

    expect(wrapper.get('[data-testid="comment-thread"]').text()).toContain('Public comment');
    expect(wrapper.find('[data-testid="comment-composer"]').exists()).toBe(false);
  });

  it('shows the composer when commenting is available', () => {
    const wrapper = mountList(true);

    expect(wrapper.find('[data-testid="comment-composer"]').exists()).toBe(true);
  });
});
