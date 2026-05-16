<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { Paginator } from '@/types/domain';

defineProps<{
  paginator: Paginator<unknown>;
}>();
</script>

<template>
  <nav v-if="paginator.lastPage > 1" class="mt-4 flex items-center justify-between gap-3">
    <Button as="a" :href="paginator.previousPageUrl ?? '#'" variant="outline" :disabled="!paginator.previousPageUrl">
      Previous
    </Button>
    <span class="text-sm text-muted-foreground">
      Page {{ paginator.currentPage }} of {{ paginator.lastPage }}
    </span>
    <Button as="a" :href="paginator.nextPageUrl ?? '#'" variant="outline" :disabled="!paginator.nextPageUrl">
      Next
    </Button>
  </nav>
</template>
