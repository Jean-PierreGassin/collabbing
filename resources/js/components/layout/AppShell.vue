<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ChevronDown, LayoutDashboard, Lightbulb, LogOut, Menu, Pencil, Plus, Search, UserCircle, X } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import CsrfField from '@/components/forms/CsrfField.vue';
import AppFooter from '@/components/layout/AppFooter.vue';
import FlashMessages from '@/components/layout/FlashMessages.vue';
import ThemeModeToggle from '@/components/layout/ThemeModeToggle.vue';
import BreadcrumbBar from '@/components/navigation/BreadcrumbBar.vue';
import { useAppShellPageState } from '@/composables/useAppShellPageState';
import { useSharedPage } from '@/lib/page';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();
const page = useSharedPage();

const isMobileMenuOpen = ref(false);
const isMobileSearchOpen = ref(false);
const isAccountMenuOpen = ref(false);
const isDesktopSearchOpen = ref(false);
const desktopSearchValue = ref('');
const desktopSearchInput = ref<HTMLInputElement | null>(null);
const accountMenu = ref<HTMLElement | null>(null);

const {
  canonicalUrl,
  currentSearchTerm,
  seo,
  transitionKey,
} = useAppShellPageState();

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

const accountInitials = computed(() => {
  const nameParts = session.user?.name
    .trim()
    .split(/\s+/)
    .filter((part) => part.length > 0) ?? [];

  if (nameParts.length === 0) {
    return 'ME';
  }

  if (nameParts.length === 1) {
    return nameParts[0].slice(0, 2).toUpperCase();
  }

  return `${nameParts[0][0]}${nameParts[nameParts.length - 1][0]}`.toUpperCase();
});

const showDesktopSearch = computed(() => {
  if (!session.isAuthenticated) {
    return true;
  }

  if (currentSearchTerm.value !== '') {
    return true;
  }

  return page.component !== 'Dashboard' && page.component !== 'Ideas/Index';
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

function closeMobileNavigation(): void {
  isMobileMenuOpen.value = false;
  isMobileSearchOpen.value = false;
}

function closeAccountMenu(): void {
  isAccountMenuOpen.value = false;
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

function openDesktopSearch(): void {
  isDesktopSearchOpen.value = true;

  void nextTick(() => {
    desktopSearchInput.value?.focus();
  });
}

function closeDesktopSearch(): void {
  isDesktopSearchOpen.value = false;
}

function closeDesktopSearchAfterBlur(): void {
  window.setTimeout(() => {
    if (desktopSearchValue.value.trim() === '') {
      closeDesktopSearch();
    }
  }, 100);
}

function updateDesktopSearch(event: Event): void {
  desktopSearchValue.value = (event.target as HTMLInputElement).value;
}

function toggleAccountMenu(): void {
  isAccountMenuOpen.value = !isAccountMenuOpen.value;
}

function handleDocumentClick(event: MouseEvent): void {
  const target = event.target;

  if (!(target instanceof Node)) {
    return;
  }

  if (accountMenu.value?.contains(target)) {
    return;
  }

  closeAccountMenu();
}

function handleShellShortcut(event: KeyboardEvent): void {
  const target = event.target;

  if (target instanceof HTMLElement && (target.isContentEditable || [
    'INPUT',
    'TEXTAREA',
    'SELECT',
  ].includes(target.tagName))) {
    return;
  }

  if (event.key.toLowerCase() !== 'k') {
    return;
  }

  if (!event.metaKey && !event.ctrlKey) {
    return;
  }

  event.preventDefault();
  openDesktopSearch();
}

onMounted(() => {
  document.addEventListener('click', handleDocumentClick);
  window.addEventListener('keydown', handleShellShortcut);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick);
  window.removeEventListener('keydown', handleShellShortcut);
});

watch(
  currentSearchTerm,
  (search) => {
    desktopSearchValue.value = search;

    if (search) {
      isDesktopSearchOpen.value = true;
    }
  },
  { immediate: true },
);
</script>

<template>
  <div class="flex min-h-screen flex-col bg-background text-foreground">
    <Head :title="seo.title">
      <!-- eslint-disable vue/max-attributes-per-line -->
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
      <!-- eslint-enable vue/max-attributes-per-line -->
    </Head>

    <a
      href="#main-content"
      class="fixed left-4 top-4 z-50 -translate-y-20 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-lg transition-transform focus:translate-y-0 focus:outline-none focus:ring-2 focus:ring-ring/50"
    >
      Skip to main content
    </a>

    <header class="relative z-50 border-b border-border bg-background/95 backdrop-blur">
      <div class="mx-auto flex w-full max-w-7xl flex-col px-4 py-3 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:gap-3 lg:px-8 lg:py-4">
        <div class="flex min-h-11 items-center justify-between gap-3">
          <div class="flex min-w-0 items-center gap-3">
            <Link
              :href="brandHref"
              :aria-label="brandLabel"
              class="w-fit text-lg font-semibold text-foreground transition-colors hover:text-primary focus-visible:rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
              @click="closeMobileNavigation"
            >
              Collabbing
            </Link>
            <Transition name="chrome-swap">
              <nav
                v-if="session.isAuthenticated"
                aria-label="Primary workspace navigation"
                class="hidden items-center gap-1 lg:flex">
                <Button
                  :as="Link"
                  :href="session.routes.ideas"
                  variant="ghost"
                  size="sm">
                  <Lightbulb
                    class="size-4"
                    aria-hidden="true" />
                  Ideas
                </Button>
                <Button
                  :as="Link"
                  :href="session.routes.dashboard"
                  variant="ghost"
                  size="sm">
                  <LayoutDashboard
                    class="size-4"
                    aria-hidden="true" />
                  Dashboard
                </Button>
              </nav>
            </Transition>
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
              <Search
                class="size-5"
                aria-hidden="true" />
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
              <X
                v-if="isMobileMenuOpen"
                class="size-5"
                aria-hidden="true" />
              <Menu
                v-else
                class="size-5"
                aria-hidden="true" />
            </Button>
          </div>
        </div>

        <div class="hidden w-full flex-col gap-3 lg:flex lg:w-auto lg:flex-row lg:items-center lg:justify-end">
          <form
            v-if="showDesktopSearch"
            :class="[
              'relative flex h-10 items-center justify-end overflow-hidden transition-[width] duration-200 ease-out',
              isDesktopSearchOpen ? 'w-72 xl:w-80' : 'w-10',
            ]"
            :action="session.routes.ideas"
            method="GET"
            role="search"
          >
            <label
              for="site-search"
              class="sr-only">Search ideas</label>
            <Button
              type="button"
              variant="ghost"
              size="icon"
              :class="[
                'absolute right-0 top-0 z-10 size-10 transition-opacity duration-150',
                isDesktopSearchOpen ? 'pointer-events-none opacity-0' : 'opacity-100',
              ]"
              aria-label="Search ideas"
              @click="openDesktopSearch"
            >
              <Search
                class="size-4"
                aria-hidden="true" />
            </Button>
            <div
              :class="[
                'relative w-full transition-[opacity,transform] duration-200 ease-out',
                isDesktopSearchOpen ? 'opacity-100 translate-x-0' : 'pointer-events-none translate-x-2 opacity-0',
              ]"
            >
              <Search
                class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                aria-hidden="true" />
              <input
                id="site-search"
                ref="desktopSearchInput"
                v-model="desktopSearchValue"
                name="search"
                type="search"
                class="h-10 w-full rounded-md border border-input bg-background pl-9 pr-3 text-sm outline-none transition-colors placeholder:text-muted-foreground focus:border-primary focus:ring-2 focus:ring-ring/40"
                placeholder="Search ideas (Ctrl/Cmd+K)"
                @input="updateDesktopSearch"
                @blur="closeDesktopSearchAfterBlur"
              >
            </div>
          </form>

          <Transition
            name="chrome-swap"
            mode="out-in">
            <nav
              v-if="session.isAuthenticated"
              key="workspace-navigation"
              aria-label="Workspace navigation"
              class="flex items-center gap-2">
              <Button
                :as="Link"
                :href="session.routes.ideasCreate">
                <Plus
                  class="size-4"
                  aria-hidden="true" />
                Create an Idea
              </Button>
            </nav>
            <nav
              v-else
              key="main-navigation"
              aria-label="Main navigation"
              class="flex items-center gap-2">
              <Button
                variant="ghost"
                :as="Link"
                :href="session.routes.ideas">
                Browse Ideas
              </Button>
              <Button
                variant="ghost"
                :as="Link"
                :href="session.routes.login">
                Login
              </Button>
              <Button
                :as="Link"
                :href="session.routes.register">
                Register
              </Button>
            </nav>
          </Transition>

          <Transition name="chrome-swap">
            <div
              v-if="session.isAuthenticated"
              class="flex min-w-0 items-center justify-end gap-2">
              <div
                ref="accountMenu"
                class="relative">
                <Button
                  variant="outline"
                  type="button"
                  class="h-10 px-3"
                  :aria-expanded="isAccountMenuOpen"
                  aria-controls="account-menu"
                  :aria-label="`Open account menu for ${session.user?.name ?? 'your account'}`"
                  @click.stop="toggleAccountMenu"
                >
                  <span class="text-xs font-semibold tracking-wide">
                    {{ accountInitials }}
                  </span>
                  <ChevronDown
                    class="size-3"
                    aria-hidden="true" />
                </Button>
                <Transition name="mobile-panel">
                  <div
                    v-if="isAccountMenuOpen"
                    id="account-menu"
                    class="absolute right-0 top-full z-[60] mt-2 flex w-72 flex-col gap-2 rounded-md border border-border bg-popover p-2 text-sm shadow-xl shadow-black/25"
                    @click.stop
                  >
                    <div class="min-w-0 px-2 pb-1 pt-1">
                      <p class="truncate text-sm font-medium text-foreground">
                        {{ session.user?.name }}
                      </p>
                    </div>
                    <Button
                      :as="Link"
                      :href="session.user?.routes.show ?? session.routes.dashboard"
                      variant="ghost"
                      class="h-10 justify-start"
                      @click="closeAccountMenu">
                      <UserCircle
                        class="size-4"
                        aria-hidden="true" />
                      View profile
                    </Button>
                    <Button
                      :as="Link"
                      :href="session.user?.routes.edit ?? session.routes.dashboard"
                      variant="ghost"
                      class="h-10 justify-start"
                      @click="closeAccountMenu">
                      <Pencil
                        class="size-4"
                        aria-hidden="true" />
                      Edit profile
                    </Button>
                    <div class="border-y border-border py-2">
                      <ThemeModeToggle
                        show-labels
                        class="w-full" />
                    </div>
                    <form
                      :action="session.routes.logout"
                      method="POST">
                      <CsrfField />
                      <Button
                        type="submit"
                        variant="ghost"
                        class="h-10 w-full justify-start">
                        <LogOut
                          class="size-4"
                          aria-hidden="true" />
                        Logout
                      </Button>
                    </form>
                  </div>
                </Transition>
              </div>
            </div>
          </Transition>
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
            <label
              for="mobile-search"
              class="sr-only">Search ideas</label>
            <Search
              class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
              aria-hidden="true" />
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
          <div
            v-if="isMobileMenuOpen"
            id="mobile-navigation"
            class="mt-3 border-t border-border pt-3 lg:hidden">
            <div
              v-if="session.isAuthenticated"
              class="grid gap-4">
              <nav
                aria-label="Mobile workspace navigation"
                class="grid gap-1">
                <p class="px-3 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                  Navigate
                </p>
                <Button
                  :as="Link"
                  :href="session.routes.ideas"
                  variant="ghost"
                  class="h-11 justify-start"
                  @click="closeMobileNavigation">
                  <Lightbulb
                    class="size-4"
                    aria-hidden="true" />
                  Ideas
                </Button>
                <Button
                  :as="Link"
                  :href="session.routes.dashboard"
                  variant="ghost"
                  class="h-11 justify-start"
                  @click="closeMobileNavigation">
                  <LayoutDashboard
                    class="size-4"
                    aria-hidden="true" />
                  Dashboard
                </Button>
              </nav>

              <div class="grid gap-1">
                <p class="px-3 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                  Create
                </p>
                <Button
                  :as="Link"
                  :href="session.routes.ideasCreate"
                  class="h-11 justify-start"
                  @click="closeMobileNavigation">
                  <Plus
                    class="size-4"
                    aria-hidden="true" />
                  Create an Idea
                </Button>
              </div>

              <nav
                aria-label="Mobile account navigation"
                class="grid gap-1">
                <p class="px-3 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                  Account
                </p>
                <Button
                  :as="Link"
                  :href="session.user?.routes.show ?? session.routes.dashboard"
                  variant="outline"
                  class="h-11 justify-start"
                  @click="closeMobileNavigation">
                  <UserCircle
                    class="size-4"
                    aria-hidden="true" />
                  <span class="truncate">{{ session.user?.name }}</span>
                </Button>
                <Button
                  :as="Link"
                  :href="session.user?.routes.edit ?? session.routes.dashboard"
                  variant="ghost"
                  class="h-11 justify-start"
                  @click="closeMobileNavigation">
                  <Pencil
                    class="size-4"
                    aria-hidden="true" />
                  Edit profile
                </Button>
              </nav>
            </div>
            <nav
              v-else
              aria-label="Mobile main navigation"
              class="grid gap-2">
              <Button
                :as="Link"
                :href="session.routes.ideas"
                variant="ghost"
                class="h-11 justify-start"
                @click="closeMobileNavigation">
                Browse Ideas
              </Button>
              <Button
                :as="Link"
                :href="session.routes.login"
                variant="ghost"
                class="h-11 justify-start"
                @click="closeMobileNavigation">
                Login
              </Button>
              <Button
                :as="Link"
                :href="session.routes.register"
                class="h-11 justify-start"
                @click="closeMobileNavigation">
                Register
              </Button>
            </nav>
            <form
              v-if="session.isAuthenticated"
              :action="session.routes.logout"
              method="POST"
              class="mt-4">
              <CsrfField />
              <Button
                type="submit"
                variant="ghost"
                class="h-11 w-full justify-start">
                <LogOut
                  class="size-4"
                  aria-hidden="true" />
                Logout
              </Button>
            </form>
            <div class="mt-3 border-t border-border pt-3">
              <p
                v-if="session.isAuthenticated"
                class="mb-2 px-3 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                Preferences
              </p>
              <ThemeModeToggle
                show-labels
                class="w-full" />
            </div>
          </div>
        </Transition>
      </div>
    </header>

    <BreadcrumbBar />

    <main
      id="main-content"
      tabindex="-1"
      class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 px-4 py-8 outline-none sm:px-6 lg:px-8">
      <FlashMessages />
      <div class="route-transition-frame">
        <Transition
          name="route-fade"
          appear>
          <div
            :key="transitionKey"
            class="route-transition-panel">
            <slot />
          </div>
        </Transition>
      </div>
    </main>

    <AppFooter />
  </div>
</template>
