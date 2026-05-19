<script setup lang="ts">
import { computed } from 'vue';
import { Monitor, Moon, Sun } from '@lucide/vue';
import { setThemeMode, useThemeMode, type ThemeMode } from '@/lib/theme';
import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{
  showLabels?: boolean;
}>(), {
  showLabels: false,
});

const { themeMode } = useThemeMode();

const options: Array<{
  icon: typeof Sun;
  label: string;
  mode: ThemeMode;
}> = [
  {
    icon: Sun,
    label: 'Light',
    mode: 'light',
  },
  {
    icon: Moon,
    label: 'Dark',
    mode: 'dark',
  },
  {
    icon: Monitor,
    label: 'System',
    mode: 'system',
  },
];

const groupLabel = computed(() => `Theme: ${themeMode.value}`);

function buttonClass(mode: ThemeMode): string {
  return cn(
    'inline-flex h-9 items-center justify-center gap-2 rounded-sm px-2.5 text-sm font-medium text-muted-foreground transition-[background-color,color,box-shadow,transform] duration-150 ease-out hover:bg-background/70 hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50',
    props.showLabels ? 'flex-1' : 'w-9',
    themeMode.value === mode && 'bg-primary/18 text-accent-foreground shadow-sm shadow-primary/10 hover:bg-primary/24 hover:text-accent-foreground dark:bg-primary dark:text-primary-foreground dark:shadow-primary/15 dark:hover:bg-primary dark:hover:text-primary-foreground',
  );
}
</script>

<template>
  <div
    class="inline-flex items-center gap-1 rounded-md border border-border bg-secondary/55 p-1"
    role="group"
    :aria-label="groupLabel"
  >
    <button
      v-for="option in options"
      :key="option.mode"
      type="button"
      :class="buttonClass(option.mode)"
      :aria-label="`Use ${option.label.toLowerCase()} theme`"
      :aria-pressed="themeMode === option.mode"
      @click="setThemeMode(option.mode)"
    >
      <component :is="option.icon" class="size-4" aria-hidden="true" />
      <span v-if="showLabels">{{ option.label }}</span>
    </button>
  </div>
</template>
