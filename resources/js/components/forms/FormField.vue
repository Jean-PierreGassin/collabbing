<script setup lang="ts">
import { computed } from 'vue';
import { fieldErrors } from '@/lib/forms';

const props = withDefaults(defineProps<{
  errorKey?: string;
  id: string;
  label: string;
  hideLabel?: boolean;
  help?: string;
}>(), {
  errorKey: undefined,
  hideLabel: false,
  help: undefined,
});

const errors = computed(() => fieldErrors(props.errorKey ?? props.id));
const helpId = computed(() => {
  if (props.help) {
    return `${props.id}-help`;
  }

  return undefined;
});
const errorId = computed(() => {
  if (errors.value.length > 0) {
    return `${props.id}-error`;
  }

  return undefined;
});
const describedBy = computed(() => [helpId.value, errorId.value].filter(Boolean).join(' ') || undefined);
const labelClass = computed(() => {
  if (props.hideLabel) {
    return 'sr-only';
  }

  return 'text-sm font-medium text-foreground';
});
</script>

<template>
  <div class="flex flex-col gap-2">
    <label :for="id" :class="labelClass">{{ label }}</label>
    <slot :invalid="errors.length > 0" :described-by="describedBy" />
    <p v-if="help" :id="helpId" class="text-sm text-muted-foreground">{{ help }}</p>
    <p v-if="errors.length > 0" :id="errorId" class="text-sm text-destructive" role="alert">
      <span v-for="error in errors" :key="error" class="block">{{ error }}</span>
    </p>
  </div>
</template>
