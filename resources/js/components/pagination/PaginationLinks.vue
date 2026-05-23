<script setup lang="ts">
import { onUnmounted, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { Paginator } from '@/types/domain';

const props = defineProps<{
  label?: string;
  only?: string[];
  paginator: Paginator<unknown>;
  placement?: 'default' | 'toolbar';
}>();

const isPaging = ref(false);
const stopFinishListener = router.on('finish', () => {
  isPaging.value = false;
});

const startPaging = (event: MouseEvent): void => {
  if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
    return;
  }

  isPaging.value = true;
};

watch(
  () => [
    props.paginator.currentPage,
    props.paginator.previousPageUrl,
    props.paginator.nextPageUrl,
  ],
  () => {
    isPaging.value = false;
  },
);

function isToolbar(): boolean {
  return props.placement === 'toolbar';
}

function navLayoutClass(): string {
  if (isToolbar()) {
    return 'flex-wrap items-center justify-start sm:justify-end';
  }

  return 'mt-4 flex-col sm:flex-row sm:items-center sm:justify-between';
}

function buttonSize(): 'default' | 'sm' {
  if (isToolbar()) {
    return 'sm';
  }

  return 'default';
}

function buttonClass(): string {
  if (isToolbar()) {
    return 'w-auto';
  }

  return 'w-full sm:w-auto';
}

function pageStatusClass(): string {
  if (isToolbar()) {
    return 'order-first py-1.5 sm:order-none';
  }

  return 'order-first py-2 sm:order-none';
}

function pageStatusText(): string {
  if (isPaging.value) {
    return 'Loading page...';
  }

  return `Page ${props.paginator.currentPage} of ${props.paginator.lastPage}`;
}

onUnmounted(stopFinishListener);
</script>

<template>
  <nav
    v-if="paginator.lastPage > 1"
    :class="cn(
      'flex gap-3',
      navLayoutClass(),
    )"
    :aria-label="label ?? 'Pagination'"
  >
    <Button
      v-if="paginator.previousPageUrl"
      :as="Link"
      :href="paginator.previousPageUrl"
      :only="only"
      preserve-scroll
      preserve-state
      variant="outline"
      :size="buttonSize()"
      :class="buttonClass()"
      @click="startPaging"
    >
      <ChevronLeft
        class="size-4"
        aria-hidden="true" />
      Previous
    </Button>
    <Button
      v-else
      type="button"
      variant="outline"
      :size="buttonSize()"
      disabled
      :class="buttonClass()"
    >
      <ChevronLeft
        class="size-4"
        aria-hidden="true" />
      Previous
    </Button>
    <span
      :class="cn(
        'rounded-md border border-border bg-card px-3 text-center text-sm text-muted-foreground transition-colors',
        pageStatusClass(),
      )"
      aria-live="polite"
    >
      {{ pageStatusText() }}
    </span>
    <Button
      v-if="paginator.nextPageUrl"
      :as="Link"
      :href="paginator.nextPageUrl"
      :only="only"
      preserve-scroll
      preserve-state
      variant="outline"
      :size="buttonSize()"
      :class="buttonClass()"
      @click="startPaging"
    >
      Next
      <ChevronRight
        class="size-4"
        aria-hidden="true" />
    </Button>
    <Button
      v-else
      type="button"
      variant="outline"
      :size="buttonSize()"
      disabled
      :class="buttonClass()"
    >
      Next
      <ChevronRight
        class="size-4"
        aria-hidden="true" />
    </Button>
  </nav>
</template>
