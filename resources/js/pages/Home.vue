<script setup lang="ts">
import { computed, ref } from 'vue';
import { FlaskConical, MessageCircle, Users } from '@lucide/vue';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();

const features = [
  {
    title: 'Pitch ideas',
    description: 'Show off your ideas to the community and get real-time feedback, interest, and a sense of direction',
    icon: MessageCircle,
  },
  {
    title: 'Be part of a team',
    description: "Find collaborators to work on your idea with you, or apply to other ideas that you're interested in",
    icon: Users,
  },
  {
    title: 'Automate project flows',
    description: "Once your project is ready, don't worry about creating a repository - we've got that sorted with our automatic integrations",
    icon: FlaskConical,
  },
];

const pointer = ref({
  x: 50,
  y: 50,
});

const foregroundDrift = computed(() => ({
  transform: `translate3d(${(pointer.value.x - 50) * -0.08}px, ${(pointer.value.y - 50) * -0.07}px, 0)`,
}));

const backgroundDrift = computed(() => ({
  transform: `translate3d(${(pointer.value.x - 50) * 0.06}px, ${(pointer.value.y - 50) * 0.045}px, 0)`,
}));

const bandPosition = computed(() => ({
  backgroundPosition: `${pointer.value.x}% ${pointer.value.y}%`,
}));

function movePreview(event: PointerEvent): void {
  const bounds = (event.currentTarget as HTMLElement).getBoundingClientRect();

  pointer.value = {
    x: ((event.clientX - bounds.left) / bounds.width) * 100,
    y: ((event.clientY - bounds.top) / bounds.height) * 100,
  };
}

function resetPreview(): void {
  pointer.value = {
    x: 50,
    y: 50,
  };
}
</script>

<template>
  <section
    class="relative left-1/2 isolate -my-8 flex min-h-[calc(100svh-7rem)] w-[100dvw] max-w-[100dvw] -translate-x-1/2 items-center overflow-hidden border-y border-border bg-background px-4 py-16 sm:px-6 lg:px-8"
    @pointermove="movePreview"
    @pointerleave="resetPreview"
  >
    <div class="absolute inset-0 -z-10 bg-[linear-gradient(120deg,color-mix(in_oklab,var(--background)_90%,black)_0%,color-mix(in_oklab,var(--card)_76%,black)_46%,color-mix(in_oklab,var(--background)_86%,var(--chart-2))_100%)]" aria-hidden="true" />
    <div class="absolute inset-0 -z-10 opacity-40 transition-[background-position] duration-700 ease-out [background-image:radial-gradient(circle_at_center,color-mix(in_oklab,var(--primary)_14%,transparent),transparent_34%),linear-gradient(115deg,transparent_0%,color-mix(in_oklab,var(--primary)_10%,transparent)_20%,transparent_40%),linear-gradient(65deg,transparent_0%,color-mix(in_oklab,var(--chart-2)_10%,transparent)_24%,transparent_48%)] [background-size:120%_120%,220%_220%,220%_220%]" :style="bandPosition" aria-hidden="true" />

    <div class="absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
      <svg class="absolute left-1/2 top-[39%] h-[66rem] w-[66rem] -translate-x-1/2 -translate-y-1/2 opacity-70 transition-transform duration-700 ease-out" :style="backgroundDrift" viewBox="0 0 900 900" fill="none">
        <path d="M132 212C248 246 332 300 450 410" stroke="url(#home-converge-primary)" stroke-width="1.5" />
        <path d="M148 660C266 592 344 512 450 410" stroke="url(#home-converge-secondary)" stroke-width="1.5" />
        <path d="M772 190C648 230 560 312 450 410" stroke="url(#home-converge-primary)" stroke-width="1.5" />
        <path d="M762 642C638 586 556 508 450 410" stroke="url(#home-converge-tertiary)" stroke-width="1.5" />
        <path d="M300 410C366 394 410 394 450 410C492 428 542 428 606 410" stroke="color-mix(in oklab, var(--foreground) 12%, transparent)" stroke-width="1" />

        <g opacity="0.92">
          <rect x="352" y="320" width="196" height="144" rx="16" fill="color-mix(in oklab, var(--card) 54%, transparent)" stroke="color-mix(in oklab, var(--primary) 22%, transparent)" />
          <rect x="384" y="352" width="56" height="10" rx="5" fill="color-mix(in oklab, var(--primary) 60%, transparent)" />
          <rect x="384" y="378" width="132" height="8" rx="4" fill="color-mix(in oklab, var(--foreground) 26%, transparent)" />
          <rect x="384" y="402" width="96" height="8" rx="4" fill="color-mix(in oklab, var(--muted-foreground) 22%, transparent)" />
          <circle cx="408" cy="434" r="10" fill="color-mix(in oklab, var(--primary) 55%, transparent)" />
          <circle cx="438" cy="434" r="10" fill="color-mix(in oklab, var(--chart-2) 50%, transparent)" />
          <circle cx="468" cy="434" r="10" fill="color-mix(in oklab, var(--chart-5) 44%, transparent)" />
        </g>

        <g opacity="0.68" fill="color-mix(in oklab, var(--primary) 72%, white)">
          <circle cx="116" cy="196" r="3" />
          <circle cx="136" cy="180" r="4" />
          <circle cx="154" cy="204" r="3" />
          <circle cx="128" cy="222" r="5">
            <animate attributeName="opacity" dur="7.5s" begin="-1.2s" repeatCount="indefinite" values="1;0.1;1;1" keyTimes="0;0.08;0.24;1" />
          </circle>
          <circle cx="166" cy="230" r="3" />
          <circle cx="102" cy="226" r="3" />
          <circle cx="146" cy="246" r="4">
            <animate attributeName="opacity" dur="8.4s" begin="-5.1s" repeatCount="indefinite" values="1;1;0.12;1" keyTimes="0;0.42;0.5;1" />
          </circle>
          <circle cx="178" cy="186" r="3" />
        </g>

        <g opacity="0.62" fill="color-mix(in oklab, var(--chart-2) 72%, white)">
          <circle cx="748" cy="178" r="3" />
          <circle cx="776" cy="184" r="5">
            <animate attributeName="opacity" dur="8.25s" begin="-3s" repeatCount="indefinite" values="1;0.1;1;1" keyTimes="0;0.08;0.26;1" />
          </circle>
          <circle cx="800" cy="204" r="3" />
          <circle cx="758" cy="218" r="4" />
          <circle cx="724" cy="204" r="3" />
          <circle cx="792" cy="238" r="4">
            <animate attributeName="opacity" dur="7.9s" begin="-6s" repeatCount="indefinite" values="1;1;0.1;1" keyTimes="0;0.48;0.56;1" />
          </circle>
          <circle cx="738" cy="244" r="3" />
          <circle cx="816" cy="178" r="3" />
        </g>

        <g opacity="0.62" fill="color-mix(in oklab, var(--chart-5) 70%, white)">
          <circle cx="128" cy="630" r="4" />
          <circle cx="150" cy="658" r="5">
            <animate attributeName="opacity" dur="9s" begin="-4.4s" repeatCount="indefinite" values="1;0.12;1;1" keyTimes="0;0.08;0.24;1" />
          </circle>
          <circle cx="112" cy="676" r="3" />
          <circle cx="170" cy="686" r="3" />
          <circle cx="196" cy="654" r="4" />
          <circle cx="140" cy="704" r="3" />
          <circle cx="188" cy="714" r="4">
            <animate attributeName="opacity" dur="8.6s" begin="-2.8s" repeatCount="indefinite" values="1;1;0.14;1" keyTimes="0;0.5;0.58;1" />
          </circle>
          <circle cx="104" cy="646" r="3" />
        </g>

        <g opacity="0.64" fill="color-mix(in oklab, var(--primary) 68%, white)">
          <circle cx="740" cy="620" r="4" />
          <circle cx="764" cy="644" r="5">
            <animate attributeName="opacity" dur="8.75s" begin="-5.8s" repeatCount="indefinite" values="1;0.1;1;1" keyTimes="0;0.08;0.26;1" />
          </circle>
          <circle cx="794" cy="632" r="3" />
          <circle cx="810" cy="666" r="4" />
          <circle cx="746" cy="688" r="3" />
          <circle cx="784" cy="708" r="4">
            <animate attributeName="opacity" dur="7.8s" begin="-3.6s" repeatCount="indefinite" values="1;1;0.12;1" keyTimes="0;0.44;0.52;1" />
          </circle>
          <circle cx="718" cy="660" r="3" />
          <circle cx="826" cy="704" r="3" />
        </g>

        <circle r="5" fill="color-mix(in oklab, var(--primary) 86%, white)">
          <animateMotion dur="7.5s" begin="-1.2s" repeatCount="indefinite" path="M128 222C248 246 332 300 450 410" />
        </circle>
        <circle r="4" fill="color-mix(in oklab, var(--chart-2) 82%, white)">
          <animateMotion dur="8.25s" begin="-3s" repeatCount="indefinite" path="M776 184C648 230 560 312 450 410" />
        </circle>
        <circle r="4" fill="color-mix(in oklab, var(--chart-5) 78%, white)">
          <animateMotion dur="9s" begin="-4.4s" repeatCount="indefinite" path="M150 658C266 592 344 512 450 410" />
        </circle>
        <circle r="5" fill="color-mix(in oklab, var(--primary) 72%, white)">
          <animateMotion dur="8.75s" begin="-5.8s" repeatCount="indefinite" path="M764 644C638 586 556 508 450 410" />
        </circle>

        <defs>
          <linearGradient id="home-converge-primary" x1="120" y1="190" x2="520" y2="470" gradientUnits="userSpaceOnUse">
            <stop stop-color="color-mix(in oklab, var(--primary) 0%, transparent)" />
            <stop offset="0.42" stop-color="color-mix(in oklab, var(--primary) 44%, transparent)" />
            <stop offset="1" stop-color="color-mix(in oklab, var(--primary) 28%, transparent)" />
          </linearGradient>
          <linearGradient id="home-converge-secondary" x1="130" y1="700" x2="480" y2="430" gradientUnits="userSpaceOnUse">
            <stop stop-color="color-mix(in oklab, var(--chart-2) 0%, transparent)" />
            <stop offset="0.48" stop-color="color-mix(in oklab, var(--chart-2) 34%, transparent)" />
            <stop offset="1" stop-color="color-mix(in oklab, var(--chart-2) 22%, transparent)" />
          </linearGradient>
          <linearGradient id="home-converge-tertiary" x1="780" y1="680" x2="430" y2="430" gradientUnits="userSpaceOnUse">
            <stop stop-color="color-mix(in oklab, var(--chart-5) 0%, transparent)" />
            <stop offset="0.5" stop-color="color-mix(in oklab, var(--chart-5) 32%, transparent)" />
            <stop offset="1" stop-color="color-mix(in oklab, var(--chart-5) 20%, transparent)" />
          </linearGradient>
        </defs>
      </svg>
    </div>

    <div class="mx-auto flex w-full max-w-7xl translate-y-8 flex-col gap-8 sm:translate-y-10 lg:translate-y-14">
      <div class="flex flex-col items-center gap-8 text-center">
        <h1 class="max-w-4xl text-5xl font-semibold tracking-normal text-white sm:text-6xl lg:text-7xl">
          <b>Collaborate</b> better.
        </h1>
        <Button :as="Link" :href="session.routes.ideas" variant="outline" size="lg">
          Browse Ideas
        </Button>
      </div>

      <div class="grid gap-4 md:grid-cols-3">
        <Card v-for="feature in features" :key="feature.title" class="border-white/10 bg-card/72 shadow-2xl shadow-black/20 backdrop-blur-md">
          <CardHeader class="items-center border-b-0 pb-3 text-center">
            <div class="mb-2 flex size-12 items-center justify-center rounded-md bg-primary/10 text-primary">
              <component :is="feature.icon" class="size-6" aria-hidden="true" />
            </div>
            <CardTitle class="text-primary">{{ feature.title }}</CardTitle>
          </CardHeader>
          <CardContent class="pt-0 text-center">
            <CardDescription>{{ feature.description }}</CardDescription>
          </CardContent>
        </Card>
      </div>
    </div>
  </section>
</template>
