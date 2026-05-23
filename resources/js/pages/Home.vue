<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { Lightbulb, Users, Zap } from '@lucide/vue';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();

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

// --- Canvas collaboration animation ---

interface NetNode {
  x: number;
  y: number;
  vx: number;
  vy: number;
  r: number;
  color: string;
  phase: number;
  driftA: number;
  driftB: number;
  glowUntil: number;
}

interface NetPulse {
  fromIdx: number;
  toIdx: number;
  progress: number;
  speed: number;
  color: string;
}

const canvasRef = ref<HTMLCanvasElement | null>(null);
const sparks = ref<{ id: number; x: number; y: number }[]>([]);

let rafId = 0;
let ro: ResizeObserver | null = null;
let nodes: NetNode[] = [];
let pulses: NetPulse[] = [];
const meetCooldown = new Map<string, number>();
let palette: string[] = [];
let canvasW = 0;
let canvasH = 0;
let dpr = 1;
let frameCount = 0;
let sparkId = 0;

function spawnSpark(x: number, y: number): void {
  const id = sparkId++;
  sparks.value.push({ id, x, y });
  window.setTimeout(() => {
    sparks.value = sparks.value.filter((s) => s.id !== id);
  }, 720);
}

const CONNECTION_DIST = 138;
const MEET_DIST = 30;
const MEET_GLOW_MS = 900;
const MEET_COOLDOWN_MS = 5500;

function initNodes(): void {
  const style = getComputedStyle(document.documentElement);
  palette = [
    style.getPropertyValue('--primary').trim(),
    style.getPropertyValue('--chart-4').trim(),
  ];

  const nodeCount = Math.max(50, Math.min(90, Math.round((canvasW * canvasH) / 16000)));
  nodes = [];
  for (let i = 0; i < nodeCount; i++) {
    nodes.push({
      x: 40 + Math.random() * Math.max(canvasW - 80, 1),
      y: 40 + Math.random() * Math.max(canvasH - 80, 1),
      vx: (Math.random() - 0.5) * 0.45,
      vy: (Math.random() - 0.5) * 0.45,
      r: 1.8 + Math.random() * 2.6,
      color: palette[Math.floor(Math.random() * palette.length)],
      phase: Math.random() * Math.PI * 2,
      driftA: 0.00035 + Math.random() * 0.00045,
      driftB: 0.00035 + Math.random() * 0.00045,
      glowUntil: 0,
    });
  }
  pulses = [];
  meetCooldown.clear();
}

function resizeCanvas(canvas: HTMLCanvasElement): void {
  const parent = canvas.parentElement;
  if (!parent) return;
  const rect = parent.getBoundingClientRect();
  dpr = Math.min(window.devicePixelRatio ?? 1, 2);
  canvasW = rect.width;
  canvasH = rect.height;
  canvas.width = Math.round(canvasW * dpr);
  canvas.height = Math.round(canvasH * dpr);
  canvas.style.width = `${canvasW}px`;
  canvas.style.height = `${canvasH}px`;
  initNodes();
}

function maybeMeet(i: number, j: number, dist: number, t: number): void {
  if (dist > MEET_DIST) return;
  if (Math.random() > 0.18) return;

  const key = `${i}-${j}`;
  if ((meetCooldown.get(key) ?? 0) > t) return;
  meetCooldown.set(key, t + MEET_COOLDOWN_MS);

  const a = nodes[i];
  const b = nodes[j];
  a.glowUntil = t + MEET_GLOW_MS;
  b.glowUntil = t + MEET_GLOW_MS;

  spawnSpark((a.x + b.x) / 2, (a.y + b.y) / 2);

  pulses.push({
    fromIdx: i,
    toIdx: j,
    progress: 0,
    speed: 0.014 + Math.random() * 0.01,
    color: a.color,
  });
}

function tick(canvas: HTMLCanvasElement, t: number): void {
  frameCount++;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  ctx.clearRect(0, 0, canvasW, canvasH);

  for (let i = 0; i < nodes.length; i++) {
    const n = nodes[i];

    n.vx += Math.sin(t * n.driftA + n.phase) * 0.006;
    n.vy += Math.cos(t * n.driftB + n.phase * 1.3) * 0.006;

    if (Math.random() < 0.012) {
      n.vx += (Math.random() - 0.5) * 0.09;
      n.vy += (Math.random() - 0.5) * 0.09;
    }

    n.vx *= 0.984;
    n.vy *= 0.984;

    const spd = Math.hypot(n.vx, n.vy);
    if (spd > 0.55) {
      n.vx = (n.vx / spd) * 0.55;
      n.vy = (n.vy / spd) * 0.55;
    }

    n.x += n.vx;
    n.y += n.vy;

    const m = 28;
    if (n.x < m) n.vx += 0.055;
    if (n.x > canvasW - m) n.vx -= 0.055;
    if (n.y < m) n.vy += 0.055;
    if (n.y > canvasH - m) n.vy -= 0.055;
  }

  const checkMeetings = frameCount % 3 === 0;
  for (let i = 0; i < nodes.length; i++) {
    for (let j = i + 1; j < nodes.length; j++) {
      const dx = nodes[i].x - nodes[j].x;
      const dy = nodes[i].y - nodes[j].y;
      const d = Math.sqrt(dx * dx + dy * dy);

      if (d < CONNECTION_DIST) {
        const alpha = (1 - d / CONNECTION_DIST) * 0.22;
        ctx.globalAlpha = alpha;
        ctx.strokeStyle = nodes[i].color;
        ctx.lineWidth = 0.7;
        ctx.beginPath();
        ctx.moveTo(nodes[i].x, nodes[i].y);
        ctx.lineTo(nodes[j].x, nodes[j].y);
        ctx.stroke();

        if (checkMeetings) maybeMeet(i, j, d, t);
      }
    }
  }
  ctx.globalAlpha = 1;

  if (frameCount % 600 === 0) {
    for (const [key, until] of meetCooldown) {
      if (until < t) meetCooldown.delete(key);
    }
  }

  const alive: NetPulse[] = [];
  for (const pulse of pulses) {
    pulse.progress += pulse.speed;
    if (pulse.progress >= 1) continue;
    alive.push(pulse);

    const from = nodes[pulse.fromIdx];
    const to = nodes[pulse.toIdx];

    if (Math.hypot(from.x - to.x, from.y - to.y) > CONNECTION_DIST * 1.25) continue;

    const px = from.x + (to.x - from.x) * pulse.progress;
    const py = from.y + (to.y - from.y) * pulse.progress;
    const alpha = 0.85 * (1 - pulse.progress * 0.5);

    const grd = ctx.createRadialGradient(px, py, 0, px, py, 8);
    grd.addColorStop(0, pulse.color);
    grd.addColorStop(0.45, pulse.color);
    grd.addColorStop(1, 'transparent');
    ctx.globalAlpha = alpha;
    ctx.fillStyle = grd;
    ctx.beginPath();
    ctx.arc(px, py, 8, 0, Math.PI * 2);
    ctx.fill();
    ctx.globalAlpha = 1;
  }
  pulses = alive;

  for (let i = 0; i < nodes.length; i++) {
    const n = nodes[i];

    if (n.glowUntil > t) {
      const meetT = (n.glowUntil - t) / MEET_GLOW_MS;
      const glowR = 9 + (1 - meetT) * 12;
      const grd = ctx.createRadialGradient(n.x, n.y, 0, n.x, n.y, glowR);
      grd.addColorStop(0, n.color);
      grd.addColorStop(0.35, n.color);
      grd.addColorStop(1, 'transparent');
      ctx.globalAlpha = 0.45 * meetT;
      ctx.fillStyle = grd;
      ctx.beginPath();
      ctx.arc(n.x, n.y, glowR, 0, Math.PI * 2);
      ctx.fill();
      ctx.globalAlpha = 1;
    }

    ctx.globalAlpha = Math.max(0.32, 0.55 + Math.sin(t * 0.0009 + n.phase) * 0.2);
    ctx.fillStyle = n.color;
    ctx.beginPath();
    ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
    ctx.fill();
    ctx.globalAlpha = 1;
  }

  rafId = requestAnimationFrame((time) => tick(canvas, time));
}

onMounted(() => {
  const canvas = canvasRef.value;
  if (!canvas || !canvas.parentElement) return;

  resizeCanvas(canvas);

  ro = new ResizeObserver(() => resizeCanvas(canvas));
  ro.observe(canvas.parentElement);

  rafId = requestAnimationFrame((t) => tick(canvas, t));
});

onBeforeUnmount(() => {
  cancelAnimationFrame(rafId);
  ro?.disconnect();
});
</script>

<template>
  <section class="relative left-1/2 isolate -my-8 flex min-h-[calc(100svh-7rem)] w-[100dvw] max-w-[100dvw] -translate-x-1/2 items-center overflow-hidden border-y border-border bg-background px-4 py-16 sm:px-6 lg:px-8">
    <!-- Warm ambient gradient -->
    <div
      class="absolute inset-0 -z-30 bg-[linear-gradient(135deg,color-mix(in_oklab,var(--background)_92%,var(--primary))_0%,color-mix(in_oklab,var(--background)_82%,var(--primary))_45%,color-mix(in_oklab,var(--background)_88%,var(--chart-4))_100%)]"
      aria-hidden="true"
    />

    <!-- Full-bleed canvas (desktop only) -->
    <canvas
      ref="canvasRef"
      class="absolute inset-0 -z-20 hidden h-full w-full opacity-75 lg:block"
      aria-hidden="true"
    />

    <!-- Asymmetric left-to-right fade: opaque under the text, transparent where the network plays -->
    <div
      class="absolute inset-0 -z-10 hidden bg-[linear-gradient(100deg,var(--background)_0%,color-mix(in_oklab,var(--background)_78%,transparent)_32%,transparent_64%)] lg:block"
      aria-hidden="true"
    />

    <!-- Green sparks fired when two nodes meet on the canvas -->
    <div class="pointer-events-none absolute inset-0 hidden lg:block" aria-hidden="true">
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
        <h1 class="text-5xl font-semibold tracking-tight text-white sm:text-6xl lg:text-7xl">
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
              <path d="M3 8 C 40 2, 80 12, 120 6 S 200 4, 217 8" class="hero-underline-path" />
            </svg>
          </span>
        </h1>

        <p class="max-w-xl text-base leading-relaxed text-muted-foreground sm:text-lg">
          Bring an idea you've been chewing on. Find the people who'll help you turn it into something real.
        </p>

        <div class="flex flex-wrap items-center gap-3 pt-1">
          <Button :as="Link" :href="session.routes.ideas" size="lg">
            Browse ideas
          </Button>
          <Button
            :as="Link"
            :href="session.isAuthenticated ? session.routes.ideasCreate : session.routes.register"
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
      <div class="grid gap-10 md:grid-cols-3 md:gap-8">
        <div
          v-for="(step, idx) in steps"
          :key="step.title"
          class="relative flex flex-col items-start gap-5"
        >
          <!-- Hand-drawn dashed connector linking each step to the next -->
          <div
            v-if="idx < steps.length - 1"
            class="pointer-events-none absolute left-16 -right-8 top-7 hidden h-px bg-[repeating-linear-gradient(to_right,color-mix(in_oklab,var(--primary)_50%,transparent)_0_6px,transparent_6px_14px)] md:block"
            aria-hidden="true"
          />

          <div class="relative flex size-14 items-center justify-center rounded-full bg-[linear-gradient(135deg,color-mix(in_oklab,var(--primary)_28%,transparent),color-mix(in_oklab,var(--chart-4)_20%,transparent))] text-primary">
            <component :is="step.icon" class="size-6" aria-hidden="true" />
            <div
              class="pointer-events-none absolute inset-0 -z-10 rounded-full bg-primary/25 blur-xl"
              aria-hidden="true"
            />
          </div>

          <div class="flex flex-col gap-2">
            <h3 class="text-lg font-semibold text-foreground">{{ step.title }}</h3>
            <p class="text-sm leading-relaxed text-muted-foreground">{{ step.description }}</p>
          </div>
        </div>
      </div>
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
