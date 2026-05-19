import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
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

  it('shares live validation state with the control', async () => {
    session.errors = {};

    const wrapper = mount(FormField, {
      props: {
        id: 'username',
        label: 'Username',
        validator: (value: string) => {
          if (value === 'builder') {
            return undefined;
          }

          return 'Use a valid username.';
        },
      },
      slots: {
        default: `
          <template #default="{ invalid, describedBy, feedbackClass }">
            <input id="username" :class="feedbackClass" :aria-invalid="invalid || undefined" :aria-describedby="describedBy">
          </template>
        `,
      },
    });

    await nextTick();
    await nextTick();
    await wrapper.get('input').setValue('bad');

    expect(wrapper.get('input').attributes('aria-invalid')).toBe('true');
    expect(wrapper.get('input').classes()).toContain('form-control-feedback-invalid');
    expect(wrapper.get('#username-feedback').text()).toBe('Use a valid username.');

    await wrapper.get('input').setValue('builder');

    expect(wrapper.get('input').attributes('aria-invalid')).toBeUndefined();
    expect(wrapper.get('input').classes()).toContain('form-control-feedback-valid');
    expect(wrapper.get('#username-feedback').text()).toBe('Looks good.');
  });

  it('does not reset typed values during live validation feedback', async () => {
    session.errors = {};

    const wrapper = mount(FormField, {
      props: {
        id: 'email',
        label: 'Email address',
      },
      slots: {
        default: `
          <template #default="{ invalid, describedBy, feedbackClass }">
            <input id="email" :defaultValue="'saved@example.com'" type="email" :class="feedbackClass" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
          </template>
        `,
      },
    });

    await nextTick();
    await nextTick();
    await wrapper.get('input').setValue('person@example.com');

    expect((wrapper.get('input').element as HTMLInputElement).value).toBe('person@example.com');
    expect(wrapper.get('input').classes()).toContain('form-control-feedback-valid');
    expect(wrapper.findAll('.form-field-sparks span')).toHaveLength(8);

    await wrapper.get('button[aria-label="Clear email address"]').trigger('click');

    expect((wrapper.get('input').element as HTMLInputElement).value).toBe('');
    expect(wrapper.get('input').attributes('aria-invalid')).toBe('true');
  });

  it('does not show success just from touching an unchanged valid field', async () => {
    session.errors = {};

    const wrapper = mount(FormField, {
      props: {
        id: 'username',
        label: 'Username',
        validator: (value: string) => {
          if (value === 'builder') {
            return undefined;
          }

          return 'Use a valid username.';
        },
      },
      slots: {
        default: `
          <template #default="{ invalid, describedBy, feedbackClass }">
            <input id="username" :defaultValue="'builder'" :class="feedbackClass" :aria-invalid="invalid || undefined" :aria-describedby="describedBy">
          </template>
        `,
      },
    });

    await nextTick();
    await nextTick();
    await wrapper.get('input').trigger('blur');

    expect(wrapper.get('input').classes()).not.toContain('form-control-feedback-valid');
    expect(wrapper.find('#username-feedback').exists()).toBe(false);
  });
});
