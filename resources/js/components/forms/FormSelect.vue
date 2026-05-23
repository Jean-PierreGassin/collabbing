<script setup lang="ts">
import { useSlots } from 'vue';
import { Check, ChevronDown } from '@lucide/vue';
import { useFormSelect } from '@/composables/useFormSelect';

const props = defineProps<{
  id: string;
  name: string;
}>();

const slots = useSlots();

const {
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
} = useFormSelect(props.id, slots);
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
        :class="[
          'size-4 shrink-0 text-primary transition-transform',
          { 'rotate-180': isOpen },
        ]"
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
