<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();
</script>

<template>
  <section class="flex flex-col gap-3">
    <Button as="a" :href="session.routes.feedback" variant="link" class="w-fit px-0">Feedback</Button>
    <Button as="a" :href="session.routes.pricing" variant="link" class="w-fit px-0">Pricing</Button>
  </section>
</template>
