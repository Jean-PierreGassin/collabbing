<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { LogOut, Plus, Search } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import CsrfField from '@/components/forms/CsrfField.vue';
import FlashMessages from '@/components/layout/FlashMessages.vue';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();

const navItems = computed(() => [
  { label: 'Dashboard', href: session.routes.dashboard, auth: true },
  { label: 'Create an Idea', href: session.routes.ideasCreate, auth: true },
]);
</script>

<template>
  <div class="min-h-screen bg-background text-foreground">
    <header class="border-b border-border bg-background/95 backdrop-blur">
      <div class="mx-auto flex min-h-16 w-full max-w-7xl flex-col gap-3 px-4 py-3 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
        <Link :href="session.routes.home" class="text-lg font-semibold text-white">Collabbing</Link>

        <nav class="flex flex-wrap items-center gap-2">
          <Button
            v-for="item in navItems"
            v-show="!item.auth || session.isAuthenticated"
            :key="item.href"
            :as="Link"
            :href="item.href"
            variant="ghost"
          >
            <Plus v-if="item.label === 'Create an Idea'" class="size-4" aria-hidden="true" />
            {{ item.label }}
          </Button>
        </nav>

        <div class="flex flex-wrap items-center gap-2">
          <form class="relative" :action="session.routes.ideas" method="GET">
            <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" aria-hidden="true" />
            <input
              name="search"
              type="search"
              class="h-9 w-48 rounded-md border border-input bg-background pl-9 pr-3 text-sm outline-none transition-colors placeholder:text-muted-foreground focus:border-primary focus:ring-2 focus:ring-ring/40"
              placeholder="Find an idea"
            >
          </form>

          <template v-if="session.isAuthenticated">
            <Button variant="outline" :as="Link" :href="session.user?.routes.show ?? session.routes.dashboard">
              {{ session.user?.name }}
            </Button>
            <form :action="session.routes.logout" method="POST">
              <CsrfField />
              <Button type="submit" variant="ghost" size="icon" aria-label="Logout">
                <LogOut class="size-4" aria-hidden="true" />
              </Button>
            </form>
          </template>
          <template v-else>
            <Button variant="ghost" :as="Link" :href="session.routes.login">Login</Button>
            <Button :as="Link" :href="session.routes.register">Register</Button>
          </template>
        </div>
      </div>
    </header>

    <main class="mx-auto flex w-full max-w-7xl flex-col gap-6 px-4 py-8 sm:px-6 lg:px-8">
      <FlashMessages />
      <slot />
    </main>

    <footer class="border-t border-border">
      <div class="mx-auto flex w-full max-w-7xl justify-center px-4 py-8 text-sm text-muted-foreground sm:px-6 lg:px-8">
        <Link class="hover:text-primary" :href="session.routes.feedback">Feedback</Link>
      </div>
    </footer>
  </div>
</template>
