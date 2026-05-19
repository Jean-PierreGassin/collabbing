<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { LayoutDashboard, LogOut, Menu, Plus, Search, X } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import CsrfField from '@/components/forms/CsrfField.vue';
import FlashMessages from '@/components/layout/FlashMessages.vue';
import BreadcrumbBar from '@/components/navigation/BreadcrumbBar.vue';
import { useSharedPage } from '@/lib/page';
import { useSessionStore } from '@/stores/session';
import type { Idea, DomainUser } from '@/types/domain';

const session = useSessionStore();
const page = useSharedPage();
const isMobileMenuOpen = ref(false);
const isMobileSearchOpen = ref(false);

const brandHref = computed(() => {
  if (session.isAuthenticated) {
    return session.routes.ideas;
  }

  return session.routes.home;
});

const brandLabel = computed(() => {
  if (session.isAuthenticated) {
    return 'Browse ideas';
  }

  return 'Collabbing home';
});
const mobileSearchLabel = computed(() => {
  if (isMobileSearchOpen.value) {
    return 'Close search';
  }

  return 'Search ideas';
});
const mobileMenuLabel = computed(() => {
  if (isMobileMenuOpen.value) {
    return 'Close navigation menu';
  }

  return 'Open navigation menu';
});

const defaultDescription = 'Share early product ideas, find collaborators, and move promising projects toward real work.';

function excerpt(value: string | null | undefined, fallback: string): string {
  if (!value) {
    return fallback;
  }

  return value
    .replace(/[#*_`>\-[\]()]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
    .slice(0, 155) || fallback;
}

const seo = computed(() => {
  const idea = page.props.idea as Idea | undefined;
  const user = page.props.user as DomainUser | undefined;

  if (idea && page.component === 'Ideas/Show') {
    return {
      title: idea.titleDisplay,
      pageTitle: `${idea.titleDisplay} | Collabbing`,
      description: excerpt(idea.summary, defaultDescription),
      type: 'article',
    };
  }

  if (user && page.component === 'Users/Show') {
    return {
      title: `${user.name} (@${user.username})`,
      pageTitle: `${user.name} (@${user.username}) | Collabbing`,
      description: excerpt(user.bio, `${user.name} is a member of the Collabbing builder community.`),
      type: 'profile',
    };
  }

  let ideaFormTitle = 'Share an Idea';
  let ideaManageTitle = 'Manage Idea';
  let userFormTitle = 'Create Profile';

  if (idea) {
    ideaFormTitle = 'Edit Idea';
    ideaManageTitle = `Manage ${idea.titleDisplay}`;
  }

  if (user) {
    userFormTitle = 'Edit Profile';
  }

  const titles: Record<string, string> = {
    'Auth/Login': 'Login',
    'Auth/PasswordEmail': 'Reset Password',
    'Auth/PasswordReset': 'Choose a New Password',
    'Auth/Register': 'Register',
    'Comments/Form': 'Comment',
    Contact: 'Contact',
    Dashboard: 'Dashboard',
    Error: 'Page Error',
    Feedback: 'Feedback',
    Home: 'Collabbing',
    'Ideas/Apply': 'Apply to Collaborate',
    'Ideas/Form': ideaFormTitle,
    'Ideas/Index': 'Ideas',
    'Ideas/Manage': ideaManageTitle,
    'Pricing': 'Pricing',
    'Resources': 'Resources',
    'Users/Form': userFormTitle,
    'Users/Index': 'Members',
  };

  const title = titles[page.component] ?? 'Collabbing';

  let pageTitle = `${title} | Collabbing`;

  if (title === 'Collabbing') {
    pageTitle = title;
  }

  return {
    title,
    pageTitle,
    description: defaultDescription,
    type: 'website',
  };
});

const canonicalUrl = computed(() => {
  if (typeof window === 'undefined') {
    return undefined;
  }

  return `${window.location.origin}${page.url.split('#')[0]}`;
});

const transitionKey = computed(() => {
  const [pathAndQuery, hash = ''] = page.url.split('#');
  const [path, query = ''] = pathAndQuery.split('?');
  const params = new URLSearchParams(query);

  params.delete('comments');

  const nextQuery = params.toString();

  let nextUrl = path;

  if (nextQuery) {
    nextUrl = `${nextUrl}?${nextQuery}`;
  }

  if (hash) {
    nextUrl = `${nextUrl}#${hash}`;
  }

  return nextUrl;
});

function closeMobileNavigation(): void {
  isMobileMenuOpen.value = false;
  isMobileSearchOpen.value = false;
}

function toggleMobileMenu(): void {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;

  if (isMobileMenuOpen.value) {
    isMobileSearchOpen.value = false;
  }
}

function toggleMobileSearch(): void {
  isMobileSearchOpen.value = !isMobileSearchOpen.value;

  if (isMobileSearchOpen.value) {
    isMobileMenuOpen.value = false;
  }
}
</script>

<template>
  <div class="flex min-h-screen flex-col bg-background text-foreground">
    <Head :title="seo.title">
      <meta head-key="description" name="description" :content="seo.description">
      <meta head-key="robots" name="robots" content="index,follow">
      <meta head-key="og:title" property="og:title" :content="seo.pageTitle">
      <meta head-key="og:description" property="og:description" :content="seo.description">
      <meta head-key="og:type" property="og:type" :content="seo.type">
      <meta v-if="canonicalUrl" head-key="og:url" property="og:url" :content="canonicalUrl">
      <meta head-key="og:site_name" property="og:site_name" content="Collabbing">
      <meta head-key="twitter:card" name="twitter:card" content="summary">
      <meta head-key="twitter:title" name="twitter:title" :content="seo.pageTitle">
      <meta head-key="twitter:description" name="twitter:description" :content="seo.description">
      <link v-if="canonicalUrl" head-key="canonical" rel="canonical" :href="canonicalUrl">
      <link head-key="favicon-svg" rel="icon" type="image/svg+xml" href="/favicon.svg">
      <link head-key="manifest" rel="manifest" href="/site.webmanifest">
    </Head>

    <a
      href="#main-content"
      class="fixed left-4 top-4 z-50 -translate-y-20 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-lg transition-transform focus:translate-y-0 focus:outline-none focus:ring-2 focus:ring-ring/50"
    >
      Skip to main content
    </a>

    <header class="border-b border-border bg-background/95 backdrop-blur">
      <div class="mx-auto flex w-full max-w-7xl flex-col px-4 py-3 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:gap-3 lg:px-8 lg:py-4">
        <div class="flex min-h-11 items-center justify-between gap-3">
          <div class="flex min-w-0 items-center gap-3">
            <Link
              :href="brandHref"
              :aria-label="brandLabel"
              class="w-fit text-lg font-semibold text-white transition-colors hover:text-primary focus-visible:rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
              @click="closeMobileNavigation"
            >
              Collabbing
            </Link>
            <Button v-if="session.isAuthenticated" :as="Link" :href="session.routes.dashboard" variant="ghost" size="sm" class="hidden lg:inline-flex">
              <LayoutDashboard class="size-4" aria-hidden="true" />
              Dashboard
            </Button>
          </div>

          <div class="flex items-center gap-2 lg:hidden">
            <Button
              type="button"
              variant="ghost"
              size="icon"
              class="size-11"
              :aria-expanded="isMobileSearchOpen"
              aria-controls="mobile-site-search"
              :aria-label="mobileSearchLabel"
              @click="toggleMobileSearch"
            >
              <Search class="size-5" aria-hidden="true" />
            </Button>
            <Button
              type="button"
              variant="outline"
              size="icon"
              class="size-11"
              :aria-expanded="isMobileMenuOpen"
              aria-controls="mobile-navigation"
              :aria-label="mobileMenuLabel"
              @click="toggleMobileMenu"
            >
              <X v-if="isMobileMenuOpen" class="size-5" aria-hidden="true" />
              <Menu v-else class="size-5" aria-hidden="true" />
            </Button>
          </div>
        </div>

        <div class="hidden w-full flex-col gap-3 lg:flex lg:w-auto lg:flex-row lg:items-center lg:justify-end">
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

          <nav v-if="session.isAuthenticated" aria-label="Workspace navigation" class="flex items-center gap-2">
            <Button :as="Link" :href="session.routes.ideasCreate">
              <Plus class="size-4" aria-hidden="true" />
              Create an Idea
            </Button>
          </nav>
          <nav v-else aria-label="Main navigation" class="flex items-center gap-2">
            <Button variant="ghost" :as="Link" :href="session.routes.ideas">
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
                class="max-w-56"
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

        <Transition name="mobile-panel">
          <form
            v-if="isMobileSearchOpen"
            id="mobile-site-search"
            class="relative mt-3 lg:hidden"
            :action="session.routes.ideas"
            method="GET"
            role="search"
          >
            <label for="mobile-search" class="sr-only">Search ideas</label>
            <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" aria-hidden="true" />
            <input
              id="mobile-search"
              name="search"
              type="search"
              class="h-11 w-full rounded-md border border-input bg-background pl-9 pr-3 text-sm outline-none transition-colors placeholder:text-muted-foreground focus:border-primary focus:ring-2 focus:ring-ring/40"
              placeholder="Search ideas"
            >
          </form>
        </Transition>

        <Transition name="mobile-panel">
          <div v-if="isMobileMenuOpen" id="mobile-navigation" class="mt-3 border-t border-border pt-3 lg:hidden">
            <nav v-if="session.isAuthenticated" aria-label="Mobile workspace navigation" class="grid gap-2">
              <Button :as="Link" :href="session.routes.ideas" variant="ghost" class="h-11 justify-start" @click="closeMobileNavigation">Ideas</Button>
              <Button :as="Link" :href="session.routes.dashboard" variant="ghost" class="h-11 justify-start" @click="closeMobileNavigation">Dashboard</Button>
              <Button :as="Link" :href="session.routes.ideasCreate" class="h-11 justify-start" @click="closeMobileNavigation">
                <Plus class="size-4" aria-hidden="true" />
                Create an Idea
              </Button>
              <Button :as="Link" :href="session.user?.routes.show ?? session.routes.dashboard" variant="outline" class="h-11 justify-start" @click="closeMobileNavigation">
                <span class="truncate">{{ session.user?.name }}</span>
              </Button>
            </nav>
            <nav v-else aria-label="Mobile main navigation" class="grid gap-2">
              <Button :as="Link" :href="session.routes.ideas" variant="ghost" class="h-11 justify-start" @click="closeMobileNavigation">Browse Ideas</Button>
              <Button :as="Link" :href="session.routes.login" variant="ghost" class="h-11 justify-start" @click="closeMobileNavigation">Login</Button>
              <Button :as="Link" :href="session.routes.register" class="h-11 justify-start" @click="closeMobileNavigation">Register</Button>
            </nav>
            <form v-if="session.isAuthenticated" :action="session.routes.logout" method="POST" class="mt-2">
              <CsrfField />
              <Button type="submit" variant="ghost" class="h-11 w-full justify-start">
                <LogOut class="size-4" aria-hidden="true" />
                Logout
              </Button>
            </form>
          </div>
        </Transition>
      </div>
    </header>

    <BreadcrumbBar />

    <main id="main-content" tabindex="-1" class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 px-4 py-8 outline-none sm:px-6 lg:px-8">
      <FlashMessages />
      <div class="route-transition-frame">
        <Transition name="route-fade" appear>
          <div :key="transitionKey" class="route-transition-panel">
            <slot />
          </div>
        </Transition>
      </div>
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
            <Link class="w-fit transition-colors hover:text-primary" :href="session.routes.users">Members</Link>
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
            <Link class="w-fit transition-colors hover:text-primary" :href="session.routes.contact">Contact</Link>
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
