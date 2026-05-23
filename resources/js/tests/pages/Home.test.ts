import { mount } from '@vue/test-utils';
import { createPinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import Home from '@/pages/Home.vue';

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

function stubMatchMedia(matchesForDesktop: boolean, matchesForReducedMotion: boolean): void {
  window.matchMedia = vi.fn((query: string) => ({
    addEventListener: vi.fn(),
    addListener: vi.fn(),
    dispatchEvent: vi.fn(),
    matches: query.includes('min-width') ? matchesForDesktop : matchesForReducedMotion,
    media: query,
    onchange: null,
    removeEventListener: vi.fn(),
    removeListener: vi.fn(),
  }));
}

function mountHome() {
  return mount(Home, {
    attachTo: document.body,
    global: {
      plugins: [createPinia()],
      stubs: {
        Button: {
          props: ['as', 'href'],
          template: '<component :is="as || \'button\'" :href="href"><slot /></component>',
        },
      },
    },
  });
}

describe('Home', () => {
  beforeEach(() => {
    page.props.auth.user = null;
    stubMatchMedia(false, false);
  });

  it('preserves create idea intent for guest registration', () => {
    const wrapper = mountHome();
    const shareLink = wrapper.findAll('a').find((link) => link.text() === 'Share an idea');

    expect(shareLink?.attributes('href')).toBe('/register?next=%2Fideas%2Fcreate');
  });

  it('does not start the canvas animation for reduced motion users', () => {
    const requestAnimationFrame = vi.fn();

    stubMatchMedia(true, true);
    vi.stubGlobal('requestAnimationFrame', requestAnimationFrame);

    mountHome();

    expect(requestAnimationFrame).not.toHaveBeenCalled();
  });
});
