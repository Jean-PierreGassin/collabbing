import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import PaginationLinks from '@/components/pagination/PaginationLinks.vue';
import type { Paginator } from '@/types/domain';

const inertia = vi.hoisted(() => ({
  finishListeners: [] as Array<() => void>,
  on: vi.fn((event: string, callback: () => void) => {
    if (event === 'finish') {
      inertia.finishListeners.push(callback);
    }

    return vi.fn();
  }),
}));

vi.mock('@inertiajs/vue3', () => ({
  Link: {
    emits: ['click'],
    props: ['href'],
    setup(_props: unknown, { emit }: { emit: (event: 'click', payload: MouseEvent) => void }) {
      function click(event: MouseEvent): void {
        emit('click', event);
        event.preventDefault();
      }

      return {
        click,
      };
    },
    template: '<a :href="href" @click="click"><slot /></a>',
  },
  router: {
    on: inertia.on,
  },
}));

function paginator(overrides: Partial<Paginator<unknown>> = {}): Paginator<unknown> {
  return {
    currentPage: 1,
    items: [],
    lastPage: 2,
    nextPageUrl: '/ideas?page=2',
    previousPageUrl: null,
    ...overrides,
  };
}

describe('PaginationLinks', () => {
  it('does not render for single-page results', () => {
    const wrapper = mount(PaginationLinks, {
      props: {
        paginator: paginator({ lastPage: 1, nextPageUrl: null }),
      },
    });

    expect(wrapper.find('nav').exists()).toBe(false);
  });

  it('shows loading state for normal Inertia pagination clicks and clears it on finish', async () => {
    const wrapper = mount(PaginationLinks, {
      props: {
        label: 'Idea pages',
        paginator: paginator(),
      },
    });

    expect(wrapper.get('nav').attributes('aria-label')).toBe('Idea pages');
    expect(wrapper.text()).toContain('Page 1 of 2');

    await wrapper.get('a[href="/ideas?page=2"]').trigger('click');

    expect(wrapper.text()).toContain('Loading page...');

    inertia.finishListeners.forEach((callback) => callback());
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).toContain('Page 1 of 2');
  });

  it('does not show loading state for new-tab or modified pagination clicks', async () => {
    const wrapper = mount(PaginationLinks, {
      props: {
        paginator: paginator(),
      },
    });

    await wrapper.get('a[href="/ideas?page=2"]').trigger('click', { metaKey: true });

    expect(wrapper.text()).toContain('Page 1 of 2');
  });
});
