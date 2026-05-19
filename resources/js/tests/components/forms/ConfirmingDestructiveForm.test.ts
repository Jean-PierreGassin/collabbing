import { mount } from '@vue/test-utils';
import { createPinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import ConfirmingDestructiveForm from '@/components/forms/ConfirmingDestructiveForm.vue';

vi.mock('@inertiajs/vue3', () => ({
  usePage: () => ({
    props: {
      auth: { user: null },
      errors: {},
      flash: {},
      oldInput: {},
      routes: {},
    },
  }),
}));

beforeEach(() => {
  const token = document.createElement('meta');
  token.name = 'csrf-token';
  token.content = 'test-csrf-token';
  document.head.append(token);
});

describe('ConfirmingDestructiveForm', () => {
  it('requires confirmation while preserving spoofed method and csrf fields', async () => {
    const wrapper = mount(ConfirmingDestructiveForm, {
      global: {
        plugins: [createPinia()],
      },
      props: {
        action: '/ideas/1/applications/2',
        buttonLabel: 'Remove Collaborator',
        confirmLabel: 'Confirm removal',
        message: 'Removing this collaborator revokes their access.',
      },
    });

    const form = wrapper.get('form');

    expect(form.attributes('action')).toBe('/ideas/1/applications/2');
    expect(form.attributes('method')).toBe('POST');
    expect(wrapper.get('input[name="_token"]').attributes('value')).toBe('test-csrf-token');
    expect(wrapper.get('input[name="_method"]').attributes('value')).toBe('DELETE');
    expect(wrapper.text()).not.toContain('Confirm removal');

    await wrapper.get('button').trigger('click');

    expect(wrapper.text()).toContain('Removing this collaborator revokes their access.');
    expect(wrapper.text()).toContain('Confirm removal');
    expect(wrapper.get('input[name="_token"]').attributes('value')).toBe('test-csrf-token');
    expect(wrapper.get('input[name="_method"]').attributes('value')).toBe('DELETE');

    const cancelButton = wrapper.findAll('button').find((button) => button.text() === 'Cancel');
    expect(cancelButton).toBeDefined();

    await cancelButton?.trigger('click');

    expect(wrapper.text()).toContain('Remove Collaborator');
    expect(wrapper.text()).not.toContain('Confirm removal');
  });
});
