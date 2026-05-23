<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, useSlots, watch } from 'vue';
import type { VNode } from 'vue';
import { Check, ChevronDown } from '@lucide/vue';

type SelectOption = {
  label: string;
  value: string;
  selected: boolean;
};

const props = defineProps<{
  id: string;
  name: string;
}>();

const slots = useSlots();
const isOpen = ref(false);
const activeIndex = ref(0);
const selectedValue = ref('');
const selectRoot = ref<HTMLElement | null>(null);
const listbox = ref<HTMLElement | null>(null);
const popoverStyle = ref<Record<string, string>>({});
const listboxId = computed(() => `${props.id}-options`);

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
</script>

<template>
  <span
    ref="selectRoot"
    class="relative inline-flex min-w-36">
    <input
      type="hidden"
      :name="name"
      :value="selectedValue">
    <button
      :id="id"
      type="button"
      class="flex h-10 w-full items-center justify-between gap-3 rounded-md border border-input bg-background/80 py-2 pl-3 pr-2 text-left text-sm text-foreground shadow-sm shadow-black/5 outline-none transition-colors hover:border-primary/55 hover:bg-secondary/70 focus-visible:border-primary focus-visible:ring-2 focus-visible:ring-ring/40"
      role="combobox"
      aria-haspopup="listbox"
      :aria-expanded="isOpen"
      :aria-controls="listboxId"
      @click="toggleSelect"
      @keydown="handleButtonKeydown"
    >
      <span class="truncate">{{ selectedLabel }}</span>
      <ChevronDown
        :class="['size-4 shrink-0 text-primary transition-transform', { 'rotate-180': isOpen }]"
        aria-hidden="true" />
    </button>

    <Teleport to="body">
      <Transition name="select-popover">
        <div
          v-if="isOpen"
          :id="listboxId"
          ref="listbox"
          class="fixed z-[80] overflow-y-auto overscroll-contain rounded-md border border-border bg-popover p-1 text-sm shadow-xl shadow-black/25"
          :style="popoverStyle"
          role="listbox"
          :aria-labelledby="id"
        >
          <button
            v-for="(option, index) in options"
            :key="option.value"
            type="button"
            role="option"
            :aria-selected="option.value === selectedValue"
            :class="[
              'flex min-h-9 w-full items-center justify-between gap-3 rounded-sm px-2.5 text-left transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50',
              option.value === selectedValue ? 'bg-primary/18 text-accent-foreground dark:bg-primary dark:text-primary-foreground' : 'text-foreground hover:bg-secondary hover:text-foreground dark:hover:text-white',
              index === activeIndex && option.value !== selectedValue ? 'bg-secondary/80 text-white' : undefined,
            ]"
            @click="selectOption(option)"
            @mouseenter="activeIndex = index"
          >
            <span>{{ option.label }}</span>
            <Check
              v-if="option.value === selectedValue"
              class="size-4"
              aria-hidden="true" />
          </button>
        </div>
      </Transition>
    </Teleport>
  </span>
</template>
