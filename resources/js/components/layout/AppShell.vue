<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { LayoutDashboard, LogOut, Plus, Search } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import CsrfField from '@/components/forms/CsrfField.vue';
import FlashMessages from '@/components/layout/FlashMessages.vue';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();

const brandHref = computed(() => (session.isAuthenticated ? session.routes.ideas : session.routes.home));
const brandLabel = computed(() => (session.isAuthenticated ? 'Browse ideas' : 'Collabbing home'));
</script>

<template>
  <div class="flex min-h-screen flex-col bg-background text-foreground">
    <header class="border-b border-border bg-background/95 backdrop-blur">
      <div class="mx-auto flex w-full max-w-7xl flex-col gap-3 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
        <div class="flex items-center justify-between gap-3 lg:justify-start">
          <Link
            :href="brandHref"
            :aria-label="brandLabel"
            class="w-fit text-lg font-semibold text-white transition-colors hover:text-primary focus-visible:rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
          >
            Collabbing
          </Link>
          <Button v-if="session.isAuthenticated" :as="Link" :href="session.routes.dashboard" variant="ghost" size="sm">
            <LayoutDashboard class="size-4" aria-hidden="true" />
            Dashboard
          </Button>
        </div>

        <div class="flex w-full flex-col gap-3 lg:w-auto lg:flex-row lg:items-center lg:justify-end">
          <form class="relative w-full lg:w-72 xl:w-80" :action="session.routes.ideas" method="GET" role="search">
            <label for="site-search" class="sr-only">Search ideas</label>
            <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" aria-hidden="true" />
            <input
              id="site-search"
              name="search"
              type="search"
              class="h-10 w-full rounded-md border border-input bg-background pl-9 pr-3 text-sm outline-none transition-colors placeholder:text-muted-foreground focus:border-primary focus:ring-2 focus:ring-ring/40"
              placeholder="Search ideas"
            >
          </form>

          <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
            <nav v-if="session.isAuthenticated" aria-label="Workspace navigation" class="grid gap-2 sm:flex sm:items-center">
              <Button :as="Link" :href="session.routes.ideasCreate" class="w-full sm:w-auto">
                <Plus class="size-4" aria-hidden="true" />
                <span class="sm:hidden">New Idea</span>
                <span class="hidden sm:inline">Create an Idea</span>
              </Button>
            </nav>
            <nav v-else aria-label="Main navigation" class="grid grid-cols-2 gap-2 sm:flex sm:items-center">
              <Button variant="ghost" :as="Link" :href="session.routes.ideas" class="hidden sm:inline-flex">
                Browse Ideas
              </Button>
              <Button variant="ghost" :as="Link" :href="session.routes.login">Login</Button>
              <Button :as="Link" :href="session.routes.register">Register</Button>
            </nav>

            <template v-if="session.isAuthenticated">
              <div class="flex min-w-0 items-center justify-end gap-2">
                <Button
                  variant="outline"
                  :as="Link"
                  :href="session.user?.routes.show ?? session.routes.dashboard"
                  class="max-w-32 sm:max-w-56"
                >
                  <span class="truncate">{{ session.user?.name }}</span>
                </Button>
                <form :action="session.routes.logout" method="POST">
                  <CsrfField />
                  <Button type="submit" variant="ghost" size="icon" aria-label="Logout">
                    <LogOut class="size-4" aria-hidden="true" />
                  </Button>
                </form>
              </div>
            </template>
          </div>
        </div>
      </div>
    </header>

    <main class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 px-4 py-8 sm:px-6 lg:px-8">
      <FlashMessages />
      <slot />
    </main>

    <footer class="border-t border-border bg-card/30">
      <div class="mx-auto grid w-full max-w-7xl gap-8 px-4 py-10 text-sm sm:grid-cols-2 sm:px-6 lg:grid-cols-[minmax(0,1.6fr)_repeat(3,minmax(0,1fr))] lg:px-8">
        <div class="flex max-w-sm flex-col gap-3">
          <Link :href="brandHref" class="text-lg font-semibold text-white transition-colors hover:text-primary">
            Collabbing
          </Link>
          <p class="leading-6 text-muted-foreground">
            A focused place to share early product ideas, find collaborators, and move promising projects toward real work.
          </p>
        </div>

        <div class="flex flex-col gap-3">
          <h2 class="text-sm font-semibold text-white">Explore</h2>
          <nav aria-label="Explore links" class="flex flex-col gap-2 text-muted-foreground">
            <Link class="w-fit transition-colors hover:text-primary" :href="session.routes.ideas">Ideas</Link>
            <Link class="w-fit transition-colors hover:text-primary" :href="session.routes.pricing">Pricing</Link>
          </nav>
        </div>

        <div class="flex flex-col gap-3">
          <h2 class="text-sm font-semibold text-white">Workspace</h2>
          <nav aria-label="Workspace footer links" class="flex flex-col gap-2 text-muted-foreground">
            <template v-if="session.isAuthenticated">
              <Link class="w-fit transition-colors hover:text-primary" :href="session.routes.dashboard">Dashboard</Link>
              <Link class="w-fit transition-colors hover:text-primary" :href="session.routes.ideasCreate">Create an Idea</Link>
            </template>
            <template v-else>
              <Link class="w-fit transition-colors hover:text-primary" :href="session.routes.login">Login</Link>
              <Link class="w-fit transition-colors hover:text-primary" :href="session.routes.register">Register</Link>
            </template>
          </nav>
        </div>

        <div class="flex flex-col gap-3">
          <h2 class="text-sm font-semibold text-white">Support</h2>
          <nav aria-label="Support links" class="flex flex-col gap-2 text-muted-foreground">
            <Link class="w-fit transition-colors hover:text-primary" :href="session.routes.feedback">Feedback</Link>
            <a class="w-fit transition-colors hover:text-primary" href="mailto:jeanpierre.gassin@gmail.com?subject=Collabbing Feedback">Contact</a>
          </nav>
        </div>
      </div>

      <div class="border-t border-border">
        <div class="mx-auto flex w-full max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-muted-foreground sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
          <p>&copy; 2026 Collabbing. Built for people turning ideas into shared momentum.</p>
          <p>Community-first collaboration for early-stage builders.</p>
        </div>
      </div>
    </footer>
  </div>
</template>
