<script setup lang="ts">
import { MessageSquare, ReceiptText } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();
</script>

<template>
  <section class="flex flex-col gap-6">
    <div class="flex flex-col gap-1">
      <h1 class="text-2xl font-semibold text-white">Resources</h1>
      <p class="max-w-2xl text-sm leading-6 text-muted-foreground">
        Helpful links for support, feedback, and account planning.
      </p>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
      <Card>
        <CardHeader>
          <div class="flex items-center gap-3">
            <span class="flex size-10 items-center justify-center rounded-md bg-primary/10 text-primary">
              <MessageSquare class="size-5" aria-hidden="true" />
            </span>
            <CardTitle>Feedback</CardTitle>
          </div>
        </CardHeader>
        <CardContent class="flex flex-col gap-4">
          <p class="text-sm leading-6 text-muted-foreground">Send product feedback, report issues, or suggest improvements.</p>
          <Button as="a" :href="session.routes.feedback" class="w-fit">Submit feedback</Button>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <div class="flex items-center gap-3">
            <span class="flex size-10 items-center justify-center rounded-md bg-primary/10 text-primary">
              <ReceiptText class="size-5" aria-hidden="true" />
            </span>
            <CardTitle>Pricing</CardTitle>
          </div>
        </CardHeader>
        <CardContent class="flex flex-col gap-4">
          <p class="text-sm leading-6 text-muted-foreground">Review current plan availability and what each tier includes.</p>
          <Button as="a" :href="session.routes.pricing" variant="outline" class="w-fit">View pricing</Button>
        </CardContent>
      </Card>
    </div>
  </section>
</template>
