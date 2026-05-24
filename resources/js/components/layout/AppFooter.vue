<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();

const brandHref = computed(() => {
  if (session.isAuthenticated) {
    return session.routes.ideas;
  }

  return session.routes.home;
});

const chromeTransitionKey = computed(() => session.isAuthenticated ? 'authenticated' : 'guest');
</script>

<template>
  <footer class="border-t border-border bg-card/30">
    <div class="footer-transition-frame">
      <Transition
        name="footer-fade"
        appear>
        <div
          :key="chromeTransitionKey"
          class="footer-transition-panel">
          <div class="mx-auto grid w-full max-w-7xl gap-8 px-4 py-10 text-sm sm:grid-cols-2 sm:px-6 lg:grid-cols-[minmax(0,1.6fr)_repeat(3,minmax(0,1fr))] lg:px-8">
            <div class="flex max-w-sm flex-col gap-3">
              <Link
                :href="brandHref"
                class="text-lg font-semibold text-foreground transition-colors hover:text-primary">
                Collabbing
              </Link>
              <p class="leading-6 text-muted-foreground">
                A focused place to share early product ideas, find collaborators, and move promising projects toward real work.
              </p>
            </div>

            <div class="flex flex-col gap-3">
              <h2 class="text-sm font-semibold text-foreground">
                Explore
              </h2>
              <nav
                aria-label="Explore links"
                class="flex flex-col gap-2 text-muted-foreground">
                <Link
                  class="w-fit transition-colors hover:text-primary"
                  :href="session.routes.ideas">
                  Ideas
                </Link>
                <Link
                  class="w-fit transition-colors hover:text-primary"
                  :href="session.routes.users">
                  Members
                </Link>
                <Link
                  class="w-fit transition-colors hover:text-primary"
                  :href="session.routes.pricing">
                  Pricing
                </Link>
              </nav>
            </div>

            <div class="flex flex-col gap-3">
              <h2 class="text-sm font-semibold text-foreground">
                Workspace
              </h2>
              <nav
                aria-label="Workspace footer links"
                class="flex flex-col gap-2 text-muted-foreground">
                <template v-if="session.isAuthenticated">
                  <Link
                    class="w-fit transition-colors hover:text-primary"
                    :href="session.routes.dashboard">
                    Dashboard
                  </Link>
                  <Link
                    class="w-fit transition-colors hover:text-primary"
                    :href="session.routes.ideasCreate">
                    Create an Idea
                  </Link>
                </template>
                <template v-else>
                  <Link
                    class="w-fit transition-colors hover:text-primary"
                    :href="session.routes.login">
                    Login
                  </Link>
                  <Link
                    class="w-fit transition-colors hover:text-primary"
                    :href="session.routes.register">
                    Register
                  </Link>
                </template>
              </nav>
            </div>

            <div class="flex flex-col gap-3">
              <h2 class="text-sm font-semibold text-foreground">
                Support
              </h2>
              <nav
                aria-label="Support links"
                class="flex flex-col gap-2 text-muted-foreground">
                <Link
                  class="w-fit transition-colors hover:text-primary"
                  :href="session.routes.feedback">
                  Feedback
                </Link>
                <Link
                  class="w-fit transition-colors hover:text-primary"
                  :href="session.routes.contact">
                  Contact
                </Link>
              </nav>
            </div>
          </div>

          <div class="border-t border-border">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-muted-foreground sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
              <p>
                &copy; 2026 Collabbing. Built for people turning ideas into shared momentum.
              </p>
              <p>
                Community-first collaboration for early-stage builders.
              </p>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </footer>
</template>
