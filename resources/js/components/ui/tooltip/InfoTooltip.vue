<script setup lang="ts">
import { Info } from '@lucide/vue';
import {
  TooltipArrow,
  TooltipContent,
  TooltipPortal,
  TooltipProvider,
  TooltipRoot,
  TooltipTrigger,
} from 'reka-ui';

withDefaults(defineProps<{
  id: string;
  label: string;
  align?: 'start' | 'center' | 'end';
  side?: 'top' | 'right' | 'bottom' | 'left';
}>(), {
  align: 'center',
  side: 'top',
});
</script>

<template>
  <TooltipProvider
    :delay-duration="150"
    :skip-delay-duration="0">
    <TooltipRoot>
      <TooltipTrigger as-child>
        <button
          type="button"
          class="inline-flex size-5 shrink-0 items-center justify-center rounded-full text-muted-foreground transition-colors hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
          :aria-label="label">
          <Info
            class="size-3.5"
            aria-hidden="true" />
        </button>
      </TooltipTrigger>
      <TooltipPortal>
        <TooltipContent
          :id="id"
          :side="side"
          :align="align"
          :side-offset="8"
          class="z-[120] max-w-64 rounded-md border border-border bg-popover px-3 py-2 text-left text-xs normal-case leading-5 text-popover-foreground shadow-xl shadow-black/25">
          {{ label }}
          <TooltipArrow class="fill-popover" />
        </TooltipContent>
      </TooltipPortal>
    </TooltipRoot>
  </TooltipProvider>
</template>
