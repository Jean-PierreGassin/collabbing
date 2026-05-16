<script setup lang="ts">
import { ArrowLeft, Check, Sparkles } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();

const plans = [
  {
    name: 'Free',
    price: '$0',
    description: 'For exploring ideas and joining early collaboration threads.',
    features: ['Limited idea posts per day', 'Limited idea applications per day', 'Community feedback'],
    action: 'Sign up for free',
    href: session.routes.register,
    available: !session.isAuthenticated,
  },
  {
    name: 'Premium',
    price: '$8 USD',
    description: 'Planned for teams that need unlimited idea and repository workflows.',
    features: ['Unlimited idea posting', 'Unlimited idea applications', 'Automatic repository management'],
    action: 'Currently free for everyone',
    href: session.routes.classicIdeas,
    available: false,
  },
];
</script>

<template>
  <section class="flex flex-col gap-6">
    <Button as="a" :href="session.routes.appResources" variant="ghost" class="w-fit">
      <ArrowLeft class="size-4" aria-hidden="true" />
      Resources
    </Button>

    <div class="flex max-w-3xl flex-col gap-3">
      <Badge variant="secondary" class="w-fit">Pricing</Badge>
      <h1 class="text-3xl font-semibold tracking-normal">Choose how you collaborate.</h1>
      <p class="text-base leading-7 text-muted-foreground">
        Collabbing is currently free while the product surface migrates to Vue.
      </p>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
      <Card v-for="plan in plans" :key="plan.name">
        <CardHeader>
          <div class="flex items-start justify-between gap-4">
            <div>
              <CardTitle>{{ plan.name }}</CardTitle>
              <CardDescription class="mt-2">{{ plan.description }}</CardDescription>
            </div>
            <span class="flex size-10 items-center justify-center rounded-md bg-accent text-accent-foreground">
              <Sparkles class="size-5" aria-hidden="true" />
            </span>
          </div>
          <p class="pt-4 text-3xl font-semibold">
            {{ plan.price }}
            <span class="text-sm font-normal text-muted-foreground">/ mo</span>
          </p>
        </CardHeader>
        <CardContent class="flex flex-col gap-5">
          <ul class="flex flex-col gap-3 text-sm text-muted-foreground">
            <li v-for="feature in plan.features" :key="feature" class="flex items-center gap-2">
              <Check class="size-4 text-primary" aria-hidden="true" />
              <span>{{ feature }}</span>
            </li>
          </ul>
          <Button v-if="plan.available" as="a" :href="plan.href" class="w-full">
            {{ plan.action }}
          </Button>
          <Button v-else as="a" :href="plan.href" variant="outline" class="w-full">
            {{ plan.action }}
          </Button>
        </CardContent>
      </Card>
    </div>
  </section>
</template>
