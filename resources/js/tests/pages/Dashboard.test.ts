import { mount } from '@vue/test-utils';
import { createPinia } from 'pinia';
import { nextTick } from 'vue';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import Dashboard from '@/pages/Dashboard.vue';
import type { Idea, Paginator } from '@/types/domain';

const page = vi.hoisted(() => ({
  props: {
    auth: {
      user: null,
    },
    errors: {},
    flash: {
      status: null,
      errors: [],
    },
    oldInput: {},
    routes: {
      home: '/',
      dashboard: '/dashboard',
      login: '/login',
      logout: '/logout',
      register: '/register',
      passwordRequest: '/forgot-password',
      passwordEmail: '/forgot-password',
      passwordReset: '/reset-password',
      ideas: '/ideas',
      ideasCreate: '/ideas/create',
      ideasStore: '/ideas',
      users: '/users',
      feedback: '/feedback',
      contact: '/contact',
      pricing: '/pricing',
    },
  },
}));

vi.mock('@inertiajs/vue3', () => ({
  Link: {
    props: ['href'],
    template: '<a :href="href"><slot /></a>',
  },
  usePage: () => page,
}));

function paginator(items: Idea[] = []): Paginator<Idea> {
  return {
    currentPage: 1,
    items,
    lastPage: 1,
    nextPageUrl: null,
    previousPageUrl: null,
  };
}

function mountDashboard() {
  return mount(Dashboard, {
    attachTo: document.body,
    props: {
      keyword: null,
      ideas: paginator(),
      collaborations: paginator(),
    },
    global: {
      plugins: [createPinia()],
      stubs: {
        IdeaList: {
          props: ['ideas'],
          template: '<div data-testid="idea-list">{{ ideas.length }}</div>',
        },
        PaginationLinks: {
          props: ['label'],
          template: '<nav :aria-label="label"></nav>',
        },
      },
    },
  });
}

describe('Dashboard tabs', () => {
  beforeEach(() => {
    window.history.pushState({}, '', '/dashboard');
  });

  it('renders dashboard sections as accessible tabs', () => {
    const wrapper = mountDashboard();
    const tabs = wrapper.findAll('[role="tab"]');

    expect(wrapper.get('[role="tablist"]').attributes('aria-label')).toBe('Dashboard sections');
    expect(tabs).toHaveLength(2);
    expect(tabs[0].attributes('id')).toBe('dashboard-ideas-tab');
    expect(tabs[0].attributes('aria-controls')).toBe('dashboard-ideas-panel');
    expect(tabs[0].attributes('aria-selected')).toBe('true');
    expect(tabs[0].attributes('aria-pressed')).toBeUndefined();
    expect(tabs[1].attributes('aria-selected')).toBe('false');

    const panel = wrapper.get('[role="tabpanel"]');

    expect(panel.attributes('id')).toBe('dashboard-ideas-panel');
    expect(panel.attributes('aria-labelledby')).toBe('dashboard-ideas-tab');
  });

  it('switches panels when a tab is clicked', async () => {
    const wrapper = mountDashboard();

    await wrapper.get('#dashboard-collaborations-tab').trigger('click');

    expect(wrapper.get('#dashboard-collaborations-tab').attributes('aria-selected')).toBe('true');
    expect(wrapper.get('#dashboard-ideas-tab').attributes('tabindex')).toBe('-1');
    expect(wrapper.get('[role="tabpanel"]').attributes('id')).toBe('dashboard-collaborations-panel');
    expect(wrapper.text()).toContain('You are not collaborating on any ideas yet.');
  });

  it('preserves the collaboration tab intent from the query string', () => {
    window.history.pushState({}, '', '/dashboard?collaborations=1');

    const wrapper = mountDashboard();

    expect(wrapper.get('#dashboard-collaborations-tab').attributes('aria-selected')).toBe('true');
    expect(wrapper.get('[role="tabpanel"]').attributes('id')).toBe('dashboard-collaborations-panel');
  });

  it('supports arrow key tab activation', async () => {
    const wrapper = mountDashboard();

    await wrapper.get('#dashboard-ideas-tab').trigger('keydown', { key: 'ArrowRight' });
    await nextTick();

    expect(wrapper.get('#dashboard-collaborations-tab').attributes('aria-selected')).toBe('true');
    expect(document.activeElement?.id).toBe('dashboard-collaborations-tab');
  });
});
