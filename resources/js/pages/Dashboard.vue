<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import IdeaList from '@/components/ideas/IdeaList.vue';
import PaginationLinks from '@/components/pagination/PaginationLinks.vue';
import { Button } from '@/components/ui/button';
import { useAccessibleTabs, type AccessibleTab } from '@/lib/tabs';
import { useSessionStore } from '@/stores/session';
import { Search, X } from '@lucide/vue';
import type { Idea, Paginator } from '@/types/domain';

type DashboardTab = 'ideas' | 'collaborations';

defineProps<{
  keyword?: string | null;
  ideas: Paginator<Idea>;
  collaborations: Paginator<Idea>;
}>();

const session = useSessionStore();

const dashboardTabs = [
  {
    value: 'ideas',
    label: 'My Ideas',
    tabId: 'dashboard-ideas-tab',
    panelId: 'dashboard-ideas-panel',
  },
  {
    value: 'collaborations',
    label: "Ideas I'm collaborating on",
    tabId: 'dashboard-collaborations-tab',
    panelId: 'dashboard-collaborations-panel',
  },
] satisfies ReadonlyArray<AccessibleTab<DashboardTab> & { label: string }>;

function initialTab(): DashboardTab {
  if (new URLSearchParams(window.location.search).has('collaborations')) {
    return 'collaborations';
  }

  return 'ideas';
}

function tabVariant(tab: DashboardTab): 'ghost' | 'secondary' {
  if (activeTab.value === tab) {
    return 'secondary';
  }

  return 'ghost';
}

const {
  activeTab,
  handleTabKeydown,
  isSelected,
  selectTab,
  tabIndex,
} = useAccessibleTabs(dashboardTabs, initialTab());
</script>

<template>
  <section class="flex flex-col gap-5">
    <h1 class="sr-only">Dashboard</h1>

    <div class="flex flex-col gap-1">
      <p class="max-w-2xl text-sm leading-6 text-muted-foreground">
        Track ideas you own and collaborations you have joined.
      </p>
    </div>

    <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(16rem,22rem)_minmax(0,1fr)] md:items-center">
      <div
        class="grid w-full grid-cols-1 gap-1 rounded-md border border-border bg-card p-1 sm:w-fit sm:grid-cols-2 md:justify-self-start"
        role="tablist"
        aria-label="Dashboard sections">
        <Button
          v-for="tab in dashboardTabs"
          :id="tab.tabId"
          :key="tab.value"
          :variant="tabVariant(tab.value)"
          :aria-controls="tab.panelId"
          :aria-selected="isSelected(tab.value)"
          :tabindex="tabIndex(tab.value)"
          role="tab"
          class="h-auto min-h-10 w-full whitespace-normal px-3 py-2 text-center leading-tight sm:h-10 sm:whitespace-nowrap"
          @click="selectTab(tab.value)"
          @keydown="handleTabKeydown"
        >
          {{ tab.label }}
        </Button>
      </div>
      <form
        :action="session.routes.dashboard"
        method="GET"
        class="relative w-full md:justify-self-center"
        role="search">
        <label
          for="dashboard-search"
          class="sr-only">Search dashboard ideas</label>
        <Search
          class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
          aria-hidden="true" />
        <input
          id="dashboard-search"
          name="search"
          type="search"
          class="h-10 w-full rounded-md border border-input bg-background pl-9 pr-20 text-sm outline-none transition-colors placeholder:text-muted-foreground focus:border-primary focus:ring-2 focus:ring-ring/40"
          :value="keyword ?? ''"
          placeholder="Search your dashboard ideas"
          maxlength="80"
        >
        <div class="absolute inset-y-1 right-1 flex items-center gap-1">
          <Button
            type="submit"
            variant="ghost"
            size="icon"
            class="size-8"
            aria-label="Search dashboard ideas">
            <Search
              class="size-4"
              aria-hidden="true" />
          </Button>
          <Button
            v-if="keyword"
            :as="Link"
            :href="session.routes.dashboard"
            variant="ghost"
            size="icon"
            class="size-8"
            aria-label="Clear dashboard search">
            <X
              class="size-4"
              aria-hidden="true" />
          </Button>
        </div>
      </form>
      <Button
        :as="Link"
        :href="session.routes.ideasCreate"
        size="sm"
        class="justify-self-center md:justify-self-end">Create an Idea</Button>
    </div>

    <div
      v-if="activeTab === 'ideas'"
      id="dashboard-ideas-panel"
      role="tabpanel"
      tabindex="0"
      aria-labelledby="dashboard-ideas-tab"
    >
      <IdeaList
        v-if="ideas.items.length > 0"
        :ideas="ideas.items" />
      <p v-else>You have not shared any ideas yet. <Link
        class="text-primary hover:underline"
        :href="session.routes.ideasCreate">Create one</Link></p>
      <PaginationLinks
        :paginator="ideas"
        :only="['ideas']"
        label="My idea pages" />
    </div>

    <div
      v-else
      id="dashboard-collaborations-panel"
      role="tabpanel"
      tabindex="0"
      aria-labelledby="dashboard-collaborations-tab"
    >
      <IdeaList
        v-if="collaborations.items.length > 0"
        :ideas="collaborations.items" />
      <p v-else>You are not collaborating on any ideas yet. <Link
        class="text-primary hover:underline"
        :href="session.routes.ideas">Browse ideas</Link></p>
      <PaginationLinks
        :paginator="collaborations"
        :only="['collaborations']"
        label="Collaboration idea pages" />
    </div>
  </section>
</template>
