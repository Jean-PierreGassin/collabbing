import { mount } from '@vue/test-utils';
import { createPinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import Register from '@/pages/Auth/Register.vue';

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
  usePage: () => page,
}));

function mountRegister() {
  return mount(Register, {
    attachTo: document.body,
    global: {
      plugins: [createPinia()],
      stubs: {
        AuthMorphCard: {
          props: ['action', 'successHref'],
          template: '<div data-testid="auth-card" :data-action="action" :data-success-href="successHref"></div>',
        },
      },
    },
  });
}

describe('Register', () => {
  beforeEach(() => {
    window.history.pushState({}, '', '/register');
  });

  it('returns homepage share idea visitors to the idea form', () => {
    window.history.pushState({}, '', '/register?next=/ideas/create');

    const wrapper = mountRegister();

    expect(wrapper.get('[data-testid="auth-card"]').attributes('data-success-href')).toBe('/ideas/create');
  });

  it('falls back to ideas for unknown next destinations', () => {
    window.history.pushState({}, '', '/register?next=https%3A%2F%2Fexample.com');

    const wrapper = mountRegister();

    expect(wrapper.get('[data-testid="auth-card"]').attributes('data-success-href')).toBe('/ideas');
  });
});
