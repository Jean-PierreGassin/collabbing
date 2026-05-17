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
  () => [props.paginator.currentPage, props.paginator.previousPageUrl, props.paginator.nextPageUrl],
  () => {
    isPaging.value = false;
  }
);

onUnmounted(stopFinishListener);
</script>

<template>
  <nav
    v-if="paginator.lastPage > 1"
    :class="cn(
      'flex gap-3',
      placement === 'toolbar'
        ? 'flex-wrap items-center justify-start sm:justify-end'
        : 'mt-4 flex-col sm:flex-row sm:items-center sm:justify-between'
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
      :size="placement === 'toolbar' ? 'sm' : 'default'"
      :class="placement === 'toolbar' ? 'w-auto' : 'w-full sm:w-auto'"
      @click="startPaging"
    >
      <ChevronLeft class="size-4" aria-hidden="true" />
      Previous
    </Button>
    <Button
      v-else
      type="button"
      variant="outline"
      :size="placement === 'toolbar' ? 'sm' : 'default'"
      disabled
      :class="placement === 'toolbar' ? 'w-auto' : 'w-full sm:w-auto'"
    >
      <ChevronLeft class="size-4" aria-hidden="true" />
      Previous
    </Button>
    <span
      :class="cn(
        'rounded-md border border-border bg-card px-3 text-center text-sm text-muted-foreground transition-colors',
        placement === 'toolbar' ? 'order-first py-1.5 sm:order-none' : 'order-first py-2 sm:order-none'
      )"
      aria-live="polite"
    >
      {{ isPaging ? 'Loading page...' : `Page ${paginator.currentPage} of ${paginator.lastPage}` }}
    </span>
    <Button
      v-if="paginator.nextPageUrl"
      :as="Link"
      :href="paginator.nextPageUrl"
      :only="only"
      preserve-scroll
      preserve-state
      variant="outline"
      :size="placement === 'toolbar' ? 'sm' : 'default'"
      :class="placement === 'toolbar' ? 'w-auto' : 'w-full sm:w-auto'"
      @click="startPaging"
    >
      Next
      <ChevronRight class="size-4" aria-hidden="true" />
    </Button>
    <Button
      v-else
      type="button"
      variant="outline"
      :size="placement === 'toolbar' ? 'sm' : 'default'"
      disabled
      :class="placement === 'toolbar' ? 'w-auto' : 'w-full sm:w-auto'"
    >
      Next
      <ChevronRight class="size-4" aria-hidden="true" />
    </Button>
  </nav>
</template>
