import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { Slots, VNode } from 'vue';

export type SelectOption = {
  label: string;
  value: string;
  selected: boolean;
};

function optionFromNode(node: VNode): SelectOption {
  const props = node.props ?? {};
  let label = '';

  if (typeof node.children === 'string') {
    label = node.children;
  }

  return {
    label: label.trim(),
    value: String(props.value ?? label),
    selected: props.selected === true || props.selected === '',
  };
}

export function useFormSelect(id: string, slots: Slots) {
  const isOpen = ref(false);
  const activeIndex = ref(0);
  const selectedValue = ref('');
  const selectRoot = ref<HTMLElement | null>(null);
  const listbox = ref<HTMLElement | null>(null);
  const popoverStyle = ref<Record<string, string>>({});

  const listboxId = computed(() => `${id}-options`);

  const options = computed<SelectOption[]>(() => {
    return (slots.default?.() ?? [])
      .filter((node) => node.type === 'option')
      .map((node) => optionFromNode(node));
  });

  const selectedOption = computed(() => {
    return options.value.find((option) => option.value === selectedValue.value) ?? options.value[0];
  });

  const selectedLabel = computed(() => selectedOption.value?.label ?? 'Select an option');

  watch(options, (nextOptions) => {
    if (nextOptions.length === 0) {
      selectedValue.value = '';
      activeIndex.value = 0;

      return;
    }

    if (nextOptions.some((option) => option.value === selectedValue.value)) {
      return;
    }

    selectedValue.value = nextOptions.find((option) => option.selected)?.value ?? nextOptions[0].value;
    activeIndex.value = Math.max(0, nextOptions.findIndex((option) => option.value === selectedValue.value));
  }, { immediate: true });

  async function openSelect(): Promise<void> {
    isOpen.value = true;
    activeIndex.value = Math.max(0, options.value.findIndex((option) => option.value === selectedValue.value));
    await nextTick();
    positionPopover();
  }

  function closeSelect(): void {
    isOpen.value = false;
  }

  function toggleSelect(): void {
    if (isOpen.value) {
      closeSelect();

      return;
    }

    void openSelect();
  }

  function selectOption(option: SelectOption): void {
    selectedValue.value = option.value;
    closeSelect();
  }

  function moveActiveOption(direction: 1 | -1): void {
    if (options.value.length === 0) {
      return;
    }

    activeIndex.value = (activeIndex.value + direction + options.value.length) % options.value.length;
  }

  function handleButtonKeydown(event: KeyboardEvent): void {
    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
      event.preventDefault();

      if (!isOpen.value) {
        void openSelect();

        return;
      }

      moveActiveOption(event.key === 'ArrowDown' ? 1 : -1);

      return;
    }

    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();

      if (!isOpen.value) {
        void openSelect();

        return;
      }

      const option = options.value[activeIndex.value];

      if (option) {
        selectOption(option);
      }

      return;
    }

    if (event.key === 'Escape') {
      closeSelect();
    }
  }

  function handleDocumentClick(event: MouseEvent): void {
    if (!selectRoot.value || !(event.target instanceof Node)) {
      return;
    }

    if (!selectRoot.value.contains(event.target) && !listbox.value?.contains(event.target)) {
      closeSelect();
    }
  }

  function positionPopover(): void {
    if (!isOpen.value || !selectRoot.value) {
      popoverStyle.value = {};

      return;
    }

    const bounds = selectRoot.value.getBoundingClientRect();
    const verticalGap = 8;
    const viewportPadding = 12;
    const availableBelow = window.innerHeight - bounds.bottom - verticalGap - viewportPadding;
    const availableAbove = bounds.top - verticalGap - viewportPadding;
    const maxHeight = Math.max(144, Math.min(256, Math.max(availableBelow, availableAbove)));
    const shouldOpenAbove = availableBelow < 144 && availableAbove > availableBelow;
    const width = Math.min(bounds.width, window.innerWidth - (viewportPadding * 2));
    const left = Math.min(Math.max(viewportPadding, bounds.left), window.innerWidth - width - viewportPadding);
    const top = shouldOpenAbove
      ? Math.max(viewportPadding, bounds.top - verticalGap - maxHeight)
      : bounds.bottom + verticalGap;

    popoverStyle.value = {
      left: `${left}px`,
      maxHeight: `${maxHeight}px`,
      minWidth: `${width}px`,
      top: `${Math.min(top, window.innerHeight - viewportPadding - maxHeight)}px`,
      width: `${width}px`,
    };
  }

  onMounted(() => {
    document.addEventListener('click', handleDocumentClick);
    document.addEventListener('scroll', positionPopover, true);
    window.addEventListener('resize', positionPopover);
  });

  onBeforeUnmount(() => {
    document.removeEventListener('click', handleDocumentClick);
    document.removeEventListener('scroll', positionPopover, true);
    window.removeEventListener('resize', positionPopover);
  });

  return {
    activeIndex,
    handleButtonKeydown,
    isOpen,
    listbox,
    listboxId,
    options,
    popoverStyle,
    selectOption,
    selectRoot,
    selectedLabel,
    selectedValue,
    toggleSelect,
  };
}
