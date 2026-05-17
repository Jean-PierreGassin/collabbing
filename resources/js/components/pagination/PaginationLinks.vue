<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { Paginator } from '@/types/domain';

defineProps<{
  paginator: Paginator<unknown>;
}>();
</script>

<template>
  <nav v-if="paginator.lastPage > 1" class="mt-4 flex items-center justify-between gap-3">
    <Button v-if="paginator.previousPageUrl" as="a" :href="paginator.previousPageUrl" variant="outline">
      Previous
    </Button>
    <Button v-else type="button" variant="outline" disabled>
      Previous
    </Button>
    <span class="text-sm text-muted-foreground">
      Page {{ paginator.currentPage }} of {{ paginator.lastPage }}
    </span>
    <Button v-if="paginator.nextPageUrl" as="a" :href="paginator.nextPageUrl" variant="outline">
      Next
    </Button>
    <Button v-else type="button" variant="outline" disabled>
      Next
    </Button>
  </nav>
</template>
