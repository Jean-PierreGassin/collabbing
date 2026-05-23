<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

const {
  ariaCurrent,
  breadcrumbs,
  breadcrumbTransitionKey,
} = useBreadcrumbs();
</script>

<template>
  <div class="border-b border-primary/15 bg-primary/[0.07]">
    <nav
      class="mx-auto flex min-h-9 w-full max-w-7xl items-center overflow-x-auto px-4 py-2 text-xs font-medium sm:px-6 lg:px-8"
      aria-label="Breadcrumb">
      <div class="breadcrumb-transition-frame">
        <Transition
          name="breadcrumb-fade"
          appear>
          <ol
            :key="breadcrumbTransitionKey"
            class="breadcrumb-transition-panel flex min-w-0 items-center gap-2 whitespace-nowrap">
            <li
              v-for="(crumb, index) in breadcrumbs"
              :key="`${crumb.label}-${index}`"
              class="flex min-w-0 items-center gap-2">
              <span
                v-if="index > 0"
                class="text-primary/70"
                aria-hidden="true">/</span>
              <Link
                v-if="crumb.href && index < breadcrumbs.length - 1"
                :href="crumb.href"
                class="max-w-56 truncate rounded-sm text-primary underline-offset-4 transition-colors hover:text-white hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
              >
                {{ crumb.label }}
              </Link>
              <span
                v-else
                class="max-w-64 truncate text-white"
                :aria-current="ariaCurrent(index)">
                {{ crumb.label }}
              </span>
            </li>
          </ol>
        </Transition>
      </div>
    </nav>
  </div>
</template>
