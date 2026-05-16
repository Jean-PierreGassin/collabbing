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
</script>

<template>
  <div class="flex flex-col gap-2">
    <label :for="id" class="text-sm font-medium text-foreground">{{ label }}</label>
    <slot :invalid="errors.length > 0" />
    <p v-if="help" class="text-sm text-muted-foreground">{{ help }}</p>
    <p v-for="error in errors" :key="error" class="text-sm text-destructive">{{ error }}</p>
  </div>
</template>
