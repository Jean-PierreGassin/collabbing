<script setup lang="ts">
import { computed } from 'vue';
import { Lightbulb, Users, Zap } from '@lucide/vue';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { useCollaborationCanvas } from '@/composables/useCollaborationCanvas';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();

const shareIdeaHref = computed(() => {
  if (session.isAuthenticated) {
    return session.routes.ideasCreate;
  }

  const separator = session.routes.register.includes('?') ? '&' : '?';

  return `${session.routes.register}${separator}next=${encodeURIComponent(session.routes.ideasCreate)}`;
});

const steps = [
  {
    title: 'Float the idea',
    description: 'Drop in the rough version — even the one you haven\'t said out loud yet. See who\'s into it.',
    icon: Lightbulb,
  },
  {
    title: 'Find your people',
    description: 'Browse what others are cooking up, or wait for the right people to find their way to your idea.',
    icon: Users,
  },
  {
    title: 'Ship it',
    description: 'We sort out repos and scaffolding so your team can focus on the part you actually came here to build.',
    icon: Zap,
  },
];

const { canvasRef, sparks } = useCollaborationCanvas();
</script>

<template>
  <section class="relative left-1/2 isolate -my-8 flex min-h-[calc(100svh-7rem)] w-[100dvw] max-w-[100dvw] -translate-x-1/2 items-center overflow-hidden border-y border-border bg-background px-4 py-16 text-foreground sm:px-6 lg:px-8">
    <!-- Warm ambient gradient -->
    <div
      class="absolute inset-0 -z-30 bg-[linear-gradient(135deg,color-mix(in_oklab,var(--background)_94%,var(--primary))_0%,color-mix(in_oklab,var(--background)_84%,var(--primary))_45%,color-mix(in_oklab,var(--background)_88%,var(--chart-4))_100%)]"
      aria-hidden="true"
    />

    <!-- Full-bleed canvas (desktop only) -->
    <canvas
      ref="canvasRef"
      class="absolute inset-0 -z-20 hidden h-full w-full opacity-45 dark:opacity-75 lg:block"
      aria-hidden="true"
    />

    <!-- Asymmetric left-to-right fade: opaque under the text, transparent where the network plays -->
    <div
      class="absolute inset-0 -z-10 hidden bg-[linear-gradient(100deg,var(--background)_0%,color-mix(in_oklab,var(--background)_80%,transparent)_32%,transparent_64%)] lg:block"
      aria-hidden="true"
    />

    <!-- Green sparks fired when two nodes meet on the canvas -->
    <div
      class="pointer-events-none absolute inset-0 hidden lg:block"
      aria-hidden="true">
      <div
        v-for="spark in sparks"
        :key="spark.id"
        class="absolute -translate-x-1/2 -translate-y-1/2"
        :style="{ left: `${spark.x}px`, top: `${spark.y}px` }"
      >
        <div class="form-field-sparks relative size-4">
          <span /><span /><span /><span /><span /><span /><span /><span />
        </div>
      </div>
    </div>

    <div class="relative z-10 mx-auto flex w-full max-w-7xl flex-col gap-16 sm:gap-24">
      <!-- Left-aligned hero -->
      <div class="flex max-w-2xl flex-col items-start gap-7">
        <h1 class="text-5xl font-semibold tracking-tight text-foreground sm:text-6xl lg:text-7xl">
          Where ideas find
          <span class="relative inline-block text-primary">
            their people.
            <svg
              class="pointer-events-none absolute -bottom-2 left-0 h-2 w-full text-primary sm:-bottom-3 sm:h-3"
              viewBox="0 0 220 12"
              preserveAspectRatio="none"
              fill="none"
              stroke="currentColor"
              stroke-width="3"
              stroke-linecap="round"
              aria-hidden="true"
            >
              <path
                d="M3 8 C 40 2, 80 12, 120 6 S 200 4, 217 8"
                class="hero-underline-path" />
            </svg>
          </span>
        </h1>

        <p class="max-w-xl text-base leading-relaxed text-muted-foreground sm:text-lg">
          Bring an idea you've been chewing on. Find the people who'll help you turn it into something real.
        </p>

        <div class="flex flex-wrap items-center gap-3 pt-1">
          <Button
            :as="Link"
            :href="session.routes.ideas"
            size="lg">
            Browse ideas
          </Button>
          <Button
            :as="Link"
            :href="shareIdeaHref"
            variant="outline"
            size="lg"
          >
            Share an idea
          </Button>
        </div>

        <p class="text-sm text-muted-foreground">
          Free to join. Bring whatever you've got.
        </p>
      </div>

      <!-- Step flow -->
      <ol class="grid gap-6 md:grid-cols-3 md:gap-8">
        <li
          v-for="(step, idx) in steps"
          :key="step.title"
          class="relative grid grid-cols-[3.5rem_1fr] gap-4 md:flex md:flex-col md:items-start md:gap-5"
        >
          <!-- Connector linking each step to the next -->
          <div
            v-if="idx < steps.length - 1"
            class="pointer-events-none absolute bottom-[-1.5rem] left-7 top-14 w-px bg-[linear-gradient(to_bottom,color-mix(in_oklab,var(--primary)_54%,transparent),color-mix(in_oklab,var(--primary)_10%,transparent))] md:hidden"
            aria-hidden="true"
          />
          <div
            v-if="idx < steps.length - 1"
            class="pointer-events-none absolute left-16 -right-8 top-7 hidden h-px bg-[repeating-linear-gradient(to_right,color-mix(in_oklab,var(--primary)_50%,transparent)_0_6px,transparent_6px_14px)] md:block"
            aria-hidden="true"
          />

          <div class="relative z-10 flex size-14 items-center justify-center rounded-full border border-primary/20 bg-[linear-gradient(135deg,color-mix(in_oklab,var(--primary)_28%,var(--background)),color-mix(in_oklab,var(--chart-4)_18%,var(--background)))] text-primary shadow-sm shadow-primary/10">
            <component
              :is="step.icon"
              class="size-6"
              aria-hidden="true" />
            <div
              class="pointer-events-none absolute inset-0 -z-10 rounded-full bg-primary/18 blur-xl"
              aria-hidden="true"
            />
          </div>

          <div class="flex min-w-0 flex-col gap-2 pt-1 md:pt-0">
            <h3 class="text-lg font-semibold text-foreground">{{ step.title }}</h3>
            <p class="text-sm leading-relaxed text-muted-foreground">{{ step.description }}</p>
          </div>
        </li>
      </ol>
    </div>
  </section>
</template>

<style scoped>
.hero-underline-path {
  stroke-dasharray: 240;
  stroke-dashoffset: 240;
  animation: hero-underline-draw 720ms cubic-bezier(0.22, 1, 0.36, 1) 420ms forwards;
}

@keyframes hero-underline-draw {
  to {
    stroke-dashoffset: 0;
  }
}

@media (prefers-reduced-motion: reduce) {
  .hero-underline-path {
    animation: none;
    stroke-dashoffset: 0;
  }
}
</style>
