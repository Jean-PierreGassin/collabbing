<script setup lang="ts">
import { computed } from 'vue';
import { fieldErrors } from '@/lib/forms';

const props = withDefaults(defineProps<{
  id: string;
  label: string;
  help?: string;
}>(), {
  help: undefined,
});

const errors = computed(() => fieldErrors(props.id));
const helpId = computed(() => (props.help ? `${props.id}-help` : undefined));
const errorId = computed(() => (errors.value.length > 0 ? `${props.id}-error` : undefined));
const describedBy = computed(() => [helpId.value, errorId.value].filter(Boolean).join(' ') || undefined);
</script>

<template>
  <div class="flex flex-col gap-2">
    <label :for="id" class="text-sm font-medium text-foreground">{{ label }}</label>
    <slot :invalid="errors.length > 0" :described-by="describedBy" />
    <p v-if="help" :id="helpId" class="text-sm text-muted-foreground">{{ help }}</p>
    <p v-if="errors.length > 0" :id="errorId" class="text-sm text-destructive" role="alert">
      <span v-for="error in errors" :key="error" class="block">{{ error }}</span>
    </p>
  </div>
</template>
