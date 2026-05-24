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

    const select = wrapper.get('select[name="status"]').element as HTMLSelectElement;

    expect(select.name).toBe('status');
    expect(select.value).toBe('open');
    expect(wrapper.get('[role="combobox"]').text()).toContain('Open');

    await wrapper.get('[role="combobox"]').trigger('click');
    await nextTick();

    const options = Array.from(document.body.querySelectorAll<HTMLElement>('[role="option"]'));

    expect(options).toHaveLength(2);
    expect(options[0].getAttribute('aria-selected')).toBe('true');
    expect(options[1].getAttribute('aria-selected')).toBe('false');

    options[1].click();
    await nextTick();

    expect(select.value).toBe('closed');
    expect(wrapper.get('[role="combobox"]').text()).toContain('Closed');
    expect(document.body.querySelector('[role="listbox"]')).toBeNull();
  });

  it('can expose required browser validation while keeping the custom trigger', () => {
    const wrapper = mount(FormSelect, {
      props: {
        id: 'contribution_type',
        name: 'contribution_type',
        required: true,
      },
      slots: {
        default: `
          <option value="">Choose a contribution type</option>
          <option value="testing">Testing</option>
        `,
      },
    });

    const select = wrapper.get('select[name="contribution_type"]').element as HTMLSelectElement;

    expect(select.required).toBe(true);
    expect(select.value).toBe('');
    expect(wrapper.get('[role="combobox"]').attributes('id')).toBe('contribution_type');
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

    const select = wrapper.get('select[name="communication_style"]').element as HTMLSelectElement;

    expect(select.value).toBe('github');
    expect(wrapper.get('[role="combobox"]').text()).toContain('GitHub');

    await wrapper.get('[role="combobox"]').trigger('click');
    await nextTick();

    const discordOption = Array.from(document.body.querySelectorAll<HTMLElement>('[role="option"]'))
      .find((option) => option.textContent?.includes('Discord'));

    expect(discordOption).toBeDefined();

    discordOption?.click();
    await nextTick();

    expect(select.value).toBe('discord');
    expect(wrapper.get('[data-testid="selected"]').text()).toBe('discord');
    expect(wrapper.get('[role="combobox"]').text()).toContain('Discord');
  });
});
