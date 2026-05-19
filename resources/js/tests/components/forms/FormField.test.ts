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

  it('connects external async errors to the form control', () => {
    session.errors = {};

    const wrapper = mount(FormField, {
      props: {
        id: 'username',
        label: 'Username',
        externalErrors: ['These credentials do not match our records.'],
      },
      slots: {
        default: `
          <template #default="{ invalid, describedBy }">
            <input id="username" :aria-invalid="invalid || undefined" :aria-describedby="describedBy">
          </template>
        `,
      },
    });

    const input = wrapper.get('input');

    expect(input.attributes('aria-invalid')).toBe('true');
    expect(input.attributes('aria-describedby')).toBe('username-error');
    expect(wrapper.get('#username-error').text()).toBe('These credentials do not match our records.');
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
    expect(wrapper.get('button[aria-label="Clear email address"]').attributes('tabindex')).toBe('-1');

    await wrapper.get('button[aria-label="Clear email address"]').trigger('click');

    expect((wrapper.get('input').element as HTMLInputElement).value).toBe('');
    expect(wrapper.get('input').attributes('aria-invalid')).toBeUndefined();
    expect(wrapper.find('#email-feedback').exists()).toBe(false);
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

  it('clears with escape while keeping the clear button out of tab flow', async () => {
    session.errors = {};

    const wrapper = mount(FormField, {
      props: {
        id: 'email',
        label: 'Email address',
      },
      slots: {
        default: `
          <template #default="{ invalid, describedBy, feedbackClass }">
            <input id="email" type="email" :class="feedbackClass" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
          </template>
        `,
      },
    });

    await nextTick();
    await nextTick();
    await wrapper.get('input').setValue('person@example.com');

    expect(wrapper.get('button[aria-label="Clear email address"]').attributes('tabindex')).toBe('-1');

    await wrapper.get('input').trigger('keydown', { key: 'Escape' });

    expect((wrapper.get('input').element as HTMLInputElement).value).toBe('');
    expect(wrapper.get('input').attributes('aria-invalid')).toBeUndefined();
  });

  it('shows required messages on submit instead of while clearing live input', async () => {
    session.errors = {};

    const wrapper = mount(FormField, {
      attachTo: document.body,
      props: {
        id: 'content',
        label: 'Comment',
      },
      slots: {
        default: `
          <template #default="{ invalid, describedBy, feedbackClass }">
            <textarea id="content" form="comment-form" :class="feedbackClass" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required />
          </template>
        `,
      },
    });

    const form = document.createElement('form');
    form.id = 'comment-form';
    document.body.append(form);

    await nextTick();
    await nextTick();
    await wrapper.get('textarea').setValue('A useful thought.');
    await wrapper.get('textarea').setValue('');

    expect(wrapper.get('textarea').attributes('aria-invalid')).toBeUndefined();
    expect(wrapper.find('#content-feedback').exists()).toBe(false);

    await wrapper.get('textarea').trigger('invalid');

    expect(wrapper.get('textarea').attributes('aria-invalid')).toBe('true');
    expect(wrapper.get('#content-feedback').text()).toBe('Comment is required.');
  });

  it('delays custom empty required validators until submit', async () => {
    session.errors = {};
    const validator = vi.fn((value: string) => {
      if (value.trim() === '') {
        return 'Enter a comment.';
      }

      return undefined;
    });

    const wrapper = mount(FormField, {
      attachTo: document.body,
      props: {
        id: 'content',
        label: 'Comment',
        validator,
      },
      slots: {
        default: `
          <template #default="{ invalid, describedBy, feedbackClass }">
            <textarea id="content" form="custom-comment-form" :class="feedbackClass" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required />
          </template>
        `,
      },
    });

    const form = document.createElement('form');
    form.id = 'custom-comment-form';
    document.body.append(form);

    await nextTick();
    await nextTick();
    await wrapper.get('textarea').setValue('A useful thought.');
    await wrapper.get('textarea').setValue('');

    expect(wrapper.get('textarea').attributes('aria-invalid')).toBeUndefined();
    expect(wrapper.find('#content-feedback').exists()).toBe(false);
    expect(validator).toHaveBeenLastCalledWith('A useful thought.', expect.any(Object));

    const submitEvent = new Event('submit', { bubbles: true, cancelable: true });
    form.dispatchEvent(submitEvent);
    await nextTick();

    expect(submitEvent.defaultPrevented).toBe(true);
    expect(wrapper.get('textarea').attributes('aria-invalid')).toBe('true');
    expect(wrapper.get('#content-feedback').text()).toBe('Comment is required.');
  });
});
