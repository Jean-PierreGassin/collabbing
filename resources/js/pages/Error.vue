<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  status: 401 | 403 | 404 | 419 | 429 | 500 | 503;
}>();

const details = computed(() => {
  if (props.status === 401) {
    return {
      title: 'Sign in to continue',
      message: 'Your session could not be confirmed. Sign in again to keep working.',
    };
  }

  if (props.status === 403) {
    return {
      title: 'Access is limited',
      message: 'This workspace area is not available to your account.',
    };
  }

  if (props.status === 404) {
    return {
      title: 'Page not found',
      message: 'The page may have moved, or the link may no longer be available.',
    };
  }

  if (props.status === 419) {
    return {
      title: 'Session expired',
      message: 'Refresh the page, then try the action again.',
    };
  }

  if (props.status === 429) {
    return {
      title: 'Too many attempts',
      message: 'Give it a moment before trying again. Reading and browsing still work while writes cool down.',
    };
  }

  if (props.status === 503) {
    return {
      title: 'Temporarily unavailable',
      message: 'The workspace is paused for maintenance. Please try again shortly.',
    };
  }

  return {
    title: 'Something went wrong',
    message: 'We could not complete that request. Please try again shortly.',
  };
});
</script>

<template>
  <section class="mx-auto flex min-h-[45vh] max-w-2xl flex-col items-center justify-center gap-5 px-4 text-center">
    <p class="text-sm font-semibold uppercase tracking-wide text-blue-200">Error {{ status }}</p>
    <h1 class="text-4xl font-semibold text-white md:text-5xl">{{ details.title }}</h1>
    <p class="text-base leading-7 text-slate-200 md:text-lg">{{ details.message }}</p>
    <div class="flex flex-wrap justify-center gap-3">
      <a
        href="/ideas"
        class="inline-flex min-h-11 items-center justify-center rounded-md bg-blue-500 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-200"
      >
        Browse ideas
      </a>
      <a
        href="/"
        class="inline-flex min-h-11 items-center justify-center rounded-md border border-white/20 px-5 py-2 text-sm font-semibold text-white transition hover:border-white/40 hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-200"
      >
        Go home
      </a>
    </div>
  </section>
</template>
