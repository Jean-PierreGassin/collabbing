import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import FormField from '@/components/forms/FormField.vue';

const session = vi.hoisted(() => ({
  errors: {} as Record<string, string>,
}));

vi.mock('@/stores/session', () => ({
  useSessionStore: () => session,
}));

describe('FormField', () => {
  it('connects help and error text to the form control', () => {
    session.errors = {
      title: 'Choose a clearer title.',
    };

    const wrapper = mount(FormField, {
      props: {
        id: 'title',
        label: 'Title',
        help: 'Use a short title.',
      },
      slots: {
        default: `
          <template #default="{ invalid, describedBy }">
            <input id="title" :aria-invalid="invalid || undefined" :aria-describedby="describedBy">
          </template>
        `,
      },
    });

    const input = wrapper.get('input');

    expect(input.attributes('aria-invalid')).toBe('true');
    expect(input.attributes('aria-describedby')).toBe('title-help title-error');
    expect(wrapper.get('#title-help').text()).toBe('Use a short title.');
    expect(wrapper.get('#title-error').attributes('role')).toBe('alert');
    expect(wrapper.get('#title-error').text()).toBe('Choose a clearer title.');
  });

  it('can visually hide labels without dropping accessible label text', () => {
    session.errors = {};

    const wrapper = mount(FormField, {
      props: {
        id: 'content',
        label: 'Comment',
        hideLabel: true,
      },
      slots: {
        default: '<textarea id="content" />',
      },
    });

    expect(wrapper.get('label').classes()).toContain('sr-only');
    expect(wrapper.get('label').text()).toBe('Comment');
  });
});
