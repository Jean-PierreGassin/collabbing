<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();
</script>

<template>
  <section class="mx-auto flex w-full max-w-5xl flex-col gap-6">
    <div class="mx-auto max-w-2xl text-center">
      <h1 class="text-4xl font-semibold tracking-normal">Pricing</h1>
      <p class="mt-3 text-muted-foreground">Get the most out of Collabbing's features, choose a pricing plan below.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
      <Card>
        <CardHeader>
          <CardTitle>Free</CardTitle>
        </CardHeader>
        <CardContent class="flex flex-col gap-5">
          <h1 class="text-3xl font-semibold">
            $0
            <small class="text-base font-normal text-muted-foreground">/ mo</small>
          </h1>
          <ul class="flex flex-col gap-2 text-sm text-muted-foreground">
            <li>Limited idea post's per-day</li>
            <li>Limited idea applications per-day</li>
            <li><s>Automatic idea repository management</s></li>
          </ul>
          <Button v-if="!session.isAuthenticated" as="a" :href="session.routes.register" variant="outline">
            Sign up for free
          </Button>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Premium</CardTitle>
        </CardHeader>
        <CardContent class="flex flex-col gap-5">
          <h1 class="text-3xl font-semibold">
            $8 USD
            <small class="text-base font-normal text-muted-foreground">/ mo</small>
          </h1>
          <ul class="flex flex-col gap-2 text-sm text-muted-foreground">
            <li>Unlimited idea posting</li>
            <li>Unlimited idea applications</li>
            <li>Automatic idea repository management</li>
          </ul>
          <Button as="a" :href="session.routes.ideas" class="pointer-events-none opacity-60">
            <s>Get started</s>
          </Button>
          <h5 class="text-lg font-medium">Collabbing is currently free for everyone!</h5>
        </CardContent>
      </Card>
    </div>
  </section>
</template>
