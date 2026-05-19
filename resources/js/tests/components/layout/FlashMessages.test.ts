import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import FlashMessages from '@/components/layout/FlashMessages.vue';

const session = vi.hoisted(() => ({
  flash: {
    status: null as string | null,
    errors: [] as string[],
  },
}));

vi.mock('@/stores/session', () => ({
  useSessionStore: () => session,
}));

describe('FlashMessages', () => {
  it('announces status flashes politely', () => {
    session.flash = {
      status: 'Profile saved.',
      errors: [],
    };

    const wrapper = mount(FlashMessages);
    const toast = wrapper.get('[role="status"]');

    expect(toast.attributes('aria-live')).toBe('polite');
    expect(toast.text()).toContain('Profile saved.');
  });

  it('summarizes multiple validation errors assertively', () => {
    session.flash = {
      status: null,
      errors: ['Title is required.', 'Content is required.'],
    };

    const wrapper = mount(FlashMessages);
    const toast = wrapper.get('[role="alert"]');

    expect(toast.attributes('aria-live')).toBe('assertive');
    expect(toast.text()).toContain('2 fields need attention. Review the highlighted fields.');
  });

  it('dismisses visible notifications', async () => {
    session.flash = {
      status: 'Comment posted.',
      errors: [],
    };

    const wrapper = mount(FlashMessages);

    await wrapper.get('button[aria-label="Dismiss notification"]').trigger('click');

    expect(wrapper.find('[role="status"]').exists()).toBe(false);
  });
});
