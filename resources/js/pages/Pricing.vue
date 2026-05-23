<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();
</script>

<template>
  <section class="mx-auto flex w-full max-w-5xl flex-col gap-6">
    <div class="mx-auto max-w-2xl text-center">
      <h1 class="text-4xl font-semibold tracking-normal">Pricing</h1>
      <p class="mt-3 text-muted-foreground">Choose the workspace tier that matches how you build and collaborate.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
      <Card>
        <CardHeader>
          <CardTitle>Free</CardTitle>
        </CardHeader>
        <CardContent class="flex flex-col gap-5">
          <p class="text-3xl font-semibold">
            $0
            <small class="text-base font-normal text-muted-foreground">/ mo</small>
          </p>
          <ul class="flex flex-col gap-2 text-sm text-muted-foreground">
            <li>Limited idea posts per day</li>
            <li>Limited idea applications per day</li>
            <li><s>Automatic idea repository management</s></li>
          </ul>
          <Button
            v-if="!session.isAuthenticated"
            as="a"
            :href="session.routes.register"
            class="w-fit">
            Sign up for free
          </Button>
          <Badge
            v-else
            variant="secondary"
            class="w-fit">Current free access</Badge>
        </CardContent>
      </Card>

      <Card class="border-primary/30">
        <CardHeader>
          <div class="flex flex-wrap items-center justify-between gap-3">
            <CardTitle>Premium</CardTitle>
            <Badge variant="default">Included for now</Badge>
          </div>
        </CardHeader>
        <CardContent class="flex flex-col gap-5">
          <p class="text-3xl font-semibold">
            $8 USD
            <small class="text-base font-normal text-muted-foreground">/ mo</small>
          </p>
          <ul class="flex flex-col gap-2 text-sm text-muted-foreground">
            <li>Unlimited idea posting</li>
            <li>Unlimited idea applications</li>
            <li>Automatic idea repository management</li>
          </ul>
          <Button
            type="button"
            disabled
            class="w-fit">
            Get started
          </Button>
          <p class="text-sm font-medium text-muted-foreground">Collabbing is currently free for everyone.</p>
        </CardContent>
      </Card>
    </div>
  </section>
</template>
