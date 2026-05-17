<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import type { Paginator } from '@/types/domain';

defineProps<{
  paginator: Paginator<unknown>;
}>();

const isPaging = ref(false);
</script>

<template>
  <nav v-if="paginator.lastPage > 1" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" aria-label="Pagination">
    <Button
      v-if="paginator.previousPageUrl"
      :as="Link"
      :href="paginator.previousPageUrl"
      preserve-scroll
      variant="outline"
      class="w-full sm:w-auto"
      @click="isPaging = true"
    >
      <ChevronLeft class="size-4" aria-hidden="true" />
      Previous
    </Button>
    <Button v-else type="button" variant="outline" disabled class="w-full sm:w-auto">
      <ChevronLeft class="size-4" aria-hidden="true" />
      Previous
    </Button>
    <span class="order-first rounded-md border border-border bg-card px-3 py-2 text-center text-sm text-muted-foreground transition-colors sm:order-none" aria-live="polite">
      {{ isPaging ? 'Loading page...' : `Page ${paginator.currentPage} of ${paginator.lastPage}` }}
    </span>
    <Button
      v-if="paginator.nextPageUrl"
      :as="Link"
      :href="paginator.nextPageUrl"
      preserve-scroll
      variant="outline"
      class="w-full sm:w-auto"
      @click="isPaging = true"
    >
      Next
      <ChevronRight class="size-4" aria-hidden="true" />
    </Button>
    <Button v-else type="button" variant="outline" disabled class="w-full sm:w-auto">
      Next
      <ChevronRight class="size-4" aria-hidden="true" />
    </Button>
  </nav>
</template>
