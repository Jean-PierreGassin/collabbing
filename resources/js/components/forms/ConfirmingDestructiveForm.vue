<script setup lang="ts">
import { ref } from 'vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import { Button } from '@/components/ui/button';

const props = withDefaults(defineProps<{
  action: string;
  buttonLabel: string;
  confirmLabel: string;
  message: string;
  method?: 'DELETE';
}>(), {
  method: 'DELETE',
});

const isConfirming = ref(false);
</script>

<template>
  <form :action="props.action" method="POST" class="flex flex-wrap items-center gap-2">
    <CsrfField />
    <MethodField :method="props.method" />

    <Button v-if="!isConfirming" type="button" variant="destructive" size="sm" @click="isConfirming = true">
      {{ props.buttonLabel }}
    </Button>

    <div v-else class="flex flex-wrap items-center gap-2 rounded-md border border-destructive/30 bg-destructive/10 p-2">
      <span class="text-sm text-destructive">{{ props.message }}</span>
      <Button type="submit" variant="destructive" size="sm">
        {{ props.confirmLabel }}
      </Button>
      <Button type="button" variant="ghost" size="sm" @click="isConfirming = false">
        Cancel
      </Button>
    </div>
  </form>
</template>
