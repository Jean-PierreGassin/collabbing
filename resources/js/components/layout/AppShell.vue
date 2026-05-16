<script setup lang="ts">
import { computed } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { ArrowRight, Lightbulb, LayoutDashboard, MessageSquare, Users } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { useSessionStore } from '@/stores/session';

const route = useRoute();
const session = useSessionStore();

const navItems = computed(() => [
  { label: 'Home', to: '/', icon: Users },
  { label: 'Ideas', to: session.routes.appIdeas, icon: Lightbulb },
  { label: 'Dashboard', to: session.routes.appDashboard, icon: LayoutDashboard },
  { label: 'Resources', to: session.routes.appResources, icon: MessageSquare },
]);
</script>

<template>
  <div class="min-h-screen bg-background text-foreground">
    <header class="border-b bg-background/95 backdrop-blur">
      <div class="mx-auto flex min-h-16 w-full max-w-7xl flex-col gap-3 px-4 py-3 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
        <RouterLink to="/" class="flex items-center gap-3">
          <span class="flex size-9 items-center justify-center rounded-md bg-primary text-primary-foreground">
            <Users class="size-5" aria-hidden="true" />
          </span>
          <span class="text-lg font-semibold tracking-normal">{{ session.appName }}</span>
        </RouterLink>

        <nav class="flex flex-wrap items-center gap-1">
          <RouterLink
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            class="inline-flex h-9 items-center gap-2 rounded-md px-3 text-sm font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
            :class="{ 'bg-accent text-accent-foreground': route.path === item.to }"
          >
            <component :is="item.icon" class="size-4" aria-hidden="true" />
            {{ item.label }}
          </RouterLink>
        </nav>

        <div class="flex items-center gap-2">
          <Button v-if="session.isAuthenticated" variant="outline" as="a" :href="session.routes.classicDashboard">
            Open classic dashboard
            <ArrowRight class="size-4" aria-hidden="true" />
          </Button>
          <template v-else>
            <Button variant="ghost" as="a" :href="session.routes.login">Log in</Button>
            <Button as="a" :href="session.routes.register">Join</Button>
          </template>
        </div>
      </div>
    </header>

    <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <slot />
    </main>
  </div>
</template>
