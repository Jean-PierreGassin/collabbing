<script setup lang="ts">
import { Button } from '@/components/ui/button';
</script>

<template>
  <section class="flex max-w-3xl flex-col gap-4">
    <h3 class="text-2xl font-semibold">Submitting Feedback</h3>
    <p class="text-muted-foreground">
      If you'd like to submit feedback, shoot an email to
      <Button as="a" href="mailto:jeanpierre.gassin@gmail.com?subject=Collabbing Feedback" variant="link" class="h-auto p-0">
        Jean-Pierre Gassin
      </Button>
      and it'll be reviewed and responded to promptly.
    </p>
  </section>
</template>
