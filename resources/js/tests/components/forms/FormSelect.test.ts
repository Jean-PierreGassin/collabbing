import { mount } from '@vue/test-utils';
import { defineComponent, nextTick, ref } from 'vue';
import { describe, expect, it } from 'vitest';
import FormSelect from '@/components/forms/FormSelect.vue';

describe('FormSelect', () => {
  it('renders branded options and submits the selected value', async () => {
    const wrapper = mount(FormSelect, {
      props: {
        id: 'status',
        name: 'status',
      },
      slots: {
        default: `
          <option value="open" selected>Open</option>
          <option value="closed">Closed</option>
        `,
      },
    });

    const hiddenInput = wrapper.get('input[type="hidden"]').element as HTMLInputElement;

    expect(hiddenInput.name).toBe('status');
    expect(hiddenInput.value).toBe('open');
    expect(wrapper.get('[role="combobox"]').text()).toContain('Open');

    await wrapper.get('[role="combobox"]').trigger('click');
    await nextTick();

    const options = Array.from(document.body.querySelectorAll<HTMLElement>('[role="option"]'));

    expect(options).toHaveLength(2);
    expect(options[0].getAttribute('aria-selected')).toBe('true');
    expect(options[1].getAttribute('aria-selected')).toBe('false');

    options[1].click();
    await nextTick();

    expect(hiddenInput.value).toBe('closed');
    expect(wrapper.get('[role="combobox"]').text()).toContain('Closed');
    expect(document.body.querySelector('[role="listbox"]')).toBeNull();
  });

  it('supports reactive values with generated option lists', async () => {
    const wrapper = mount(defineComponent({
      components: {
        FormSelect,
      },
      setup() {
        const selected = ref('github');
        const options = [
          {
            label: 'GitHub',
            value: 'github',
          },
          {
            label: 'Discord',
            value: 'discord',
          },
        ];

        return {
          options,
          selected,
        };
      },
      template: `
        <FormSelect
          id="communication_style"
          v-model="selected"
          name="communication_style">
          <option
            v-for="option in options"
            :key="option.value"
            :value="option.value">
            {{ option.label }}
          </option>
        </FormSelect>
        <span data-testid="selected">{{ selected }}</span>
      `,
    }));

    const hiddenInput = wrapper.get('input[type="hidden"]').element as HTMLInputElement;

    expect(hiddenInput.value).toBe('github');
    expect(wrapper.get('[role="combobox"]').text()).toContain('GitHub');

    await wrapper.get('[role="combobox"]').trigger('click');
    await nextTick();

    const discordOption = Array.from(document.body.querySelectorAll<HTMLElement>('[role="option"]'))
      .find((option) => option.textContent?.includes('Discord'));

    expect(discordOption).toBeDefined();

    discordOption?.click();
    await nextTick();

    expect(hiddenInput.value).toBe('discord');
    expect(wrapper.get('[data-testid="selected"]').text()).toBe('discord');
    expect(wrapper.get('[role="combobox"]').text()).toContain('Discord');
  });
});
