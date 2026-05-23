import { onBeforeUnmount, onMounted, ref } from 'vue';

interface CanvasNode {
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

interface CanvasPulse {
  fromIdx: number;
  toIdx: number;
  progress: number;
  speed: number;
  color: string;
}

interface CanvasSpark {
  id: number;
  x: number;
  y: number;
}

const CONNECTION_DIST = 138;
const MEET_DIST = 30;
const MEET_GLOW_MS = 900;
const MEET_COOLDOWN_MS = 5500;

export function useCollaborationCanvas() {
  const canvasRef = ref<HTMLCanvasElement | null>(null);
  const sparks = ref<CanvasSpark[]>([]);

  let rafId = 0;
  let resizeObserver: ResizeObserver | null = null;
  let nodes: CanvasNode[] = [];
  let pulses: CanvasPulse[] = [];
  const meetCooldown = new Map<string, number>();
  let palette: string[] = [];
  let canvasW = 0;
  let canvasH = 0;
  let dpr = 1;
  let frameCount = 0;
  let sparkId = 0;
  let desktopQuery: MediaQueryList | null = null;
  let reducedMotionQuery: MediaQueryList | null = null;
  const sparkTimers = new Set<number>();

  function spawnSpark(x: number, y: number): void {
    const id = sparkId++;
    sparks.value.push({ id, x, y });
    const timerId = window.setTimeout(() => {
      sparkTimers.delete(timerId);
      sparks.value = sparks.value.filter((spark) => spark.id !== id);
    }, 720);
    sparkTimers.add(timerId);
  }

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

    if (!parent) {
      return;
    }

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

  function prefersReducedMotion(): boolean {
    if (!reducedMotionQuery) {
      return true;
    }

    return reducedMotionQuery.matches;
  }

  function canAnimateCanvas(): boolean {
    if (!desktopQuery) {
      return false;
    }

    if (!desktopQuery.matches) {
      return false;
    }

    if (prefersReducedMotion()) {
      return false;
    }

    return true;
  }

  function clearSparks(): void {
    for (const timerId of sparkTimers) {
      window.clearTimeout(timerId);
    }

    sparkTimers.clear();
    sparks.value = [];
  }

  function stopCanvasAnimation(): void {
    if (rafId) {
      cancelAnimationFrame(rafId);
      rafId = 0;
    }

    resizeObserver?.disconnect();
    resizeObserver = null;
    pulses = [];
    meetCooldown.clear();
    clearSparks();

    const canvas = canvasRef.value;

    if (!canvas || canvasW === 0 || canvasH === 0) {
      return;
    }

    const context = canvas.getContext('2d');

    if (context) {
      context.clearRect(0, 0, canvasW, canvasH);
    }
  }

  function startCanvasAnimation(): void {
    if (rafId) {
      return;
    }

    if (!canAnimateCanvas()) {
      return;
    }

    const canvas = canvasRef.value;

    if (!canvas || !canvas.parentElement) {
      return;
    }

    resizeCanvas(canvas);

    resizeObserver = new ResizeObserver(() => resizeCanvas(canvas));
    resizeObserver.observe(canvas.parentElement);

    rafId = requestAnimationFrame((time) => tick(canvas, time));
  }

  function syncCanvasAnimation(): void {
    if (canAnimateCanvas()) {
      startCanvasAnimation();

      return;
    }

    stopCanvasAnimation();
  }

  function addQueryListener(query: MediaQueryList, listener: () => void): void {
    if (query.addEventListener) {
      query.addEventListener('change', listener);

      return;
    }

    query.addListener(listener);
  }

  function removeQueryListener(query: MediaQueryList | null, listener: () => void): void {
    if (!query) {
      return;
    }

    if (query.removeEventListener) {
      query.removeEventListener('change', listener);

      return;
    }

    query.removeListener(listener);
  }

  function maybeMeet(firstIndex: number, secondIndex: number, distance: number, time: number): void {
    if (distance > MEET_DIST) {
      return;
    }

    if (Math.random() > 0.18) {
      return;
    }

    const key = `${firstIndex}-${secondIndex}`;

    if ((meetCooldown.get(key) ?? 0) > time) {
      return;
    }

    meetCooldown.set(key, time + MEET_COOLDOWN_MS);

    const first = nodes[firstIndex];
    const second = nodes[secondIndex];
    first.glowUntil = time + MEET_GLOW_MS;
    second.glowUntil = time + MEET_GLOW_MS;

    spawnSpark((first.x + second.x) / 2, (first.y + second.y) / 2);

    pulses.push({
      fromIdx: firstIndex,
      toIdx: secondIndex,
      progress: 0,
      speed: 0.014 + Math.random() * 0.01,
      color: first.color,
    });
  }

  function tick(canvas: HTMLCanvasElement, time: number): void {
    frameCount++;
    const context = canvas.getContext('2d');

    if (!context) {
      return;
    }

    context.setTransform(dpr, 0, 0, dpr, 0, 0);
    context.clearRect(0, 0, canvasW, canvasH);
    moveNodes(time);
    drawConnections(context, time);
    drawPulses(context);
    drawNodes(context, time);

    rafId = requestAnimationFrame((nextTime) => tick(canvas, nextTime));
  }

  function moveNodes(time: number): void {
    for (const node of nodes) {
      node.vx += Math.sin(time * node.driftA + node.phase) * 0.006;
      node.vy += Math.cos(time * node.driftB + node.phase * 1.3) * 0.006;

      if (Math.random() < 0.012) {
        node.vx += (Math.random() - 0.5) * 0.09;
        node.vy += (Math.random() - 0.5) * 0.09;
      }

      node.vx *= 0.984;
      node.vy *= 0.984;

      const speed = Math.hypot(node.vx, node.vy);

      if (speed > 0.55) {
        node.vx = (node.vx / speed) * 0.55;
        node.vy = (node.vy / speed) * 0.55;
      }

      node.x += node.vx;
      node.y += node.vy;
      keepNodeInBounds(node);
    }
  }

  function keepNodeInBounds(node: CanvasNode): void {
    const margin = 28;

    if (node.x < margin) {
      node.vx += 0.055;
    }

    if (node.x > canvasW - margin) {
      node.vx -= 0.055;
    }

    if (node.y < margin) {
      node.vy += 0.055;
    }

    if (node.y > canvasH - margin) {
      node.vy -= 0.055;
    }
  }

  function drawConnections(context: CanvasRenderingContext2D, time: number): void {
    const checkMeetings = frameCount % 3 === 0;

    for (let i = 0; i < nodes.length; i++) {
      for (let j = i + 1; j < nodes.length; j++) {
        const distance = distanceBetween(nodes[i], nodes[j]);

        if (distance >= CONNECTION_DIST) {
          continue;
        }

        context.globalAlpha = (1 - distance / CONNECTION_DIST) * 0.22;
        context.strokeStyle = nodes[i].color;
        context.lineWidth = 0.7;
        context.beginPath();
        context.moveTo(nodes[i].x, nodes[i].y);
        context.lineTo(nodes[j].x, nodes[j].y);
        context.stroke();

        if (checkMeetings) {
          maybeMeet(i, j, distance, time);
        }
      }
    }

    context.globalAlpha = 1;

    if (frameCount % 600 === 0) {
      pruneMeetCooldowns(time);
    }
  }

  function pruneMeetCooldowns(time: number): void {
    for (const [
      key,
      until,
    ] of meetCooldown) {
      if (until < time) {
        meetCooldown.delete(key);
      }
    }
  }

  function drawPulses(context: CanvasRenderingContext2D): void {
    const alive: CanvasPulse[] = [];

    for (const pulse of pulses) {
      pulse.progress += pulse.speed;

      if (pulse.progress >= 1) {
        continue;
      }

      alive.push(pulse);

      const from = nodes[pulse.fromIdx];
      const to = nodes[pulse.toIdx];

      if (distanceBetween(from, to) > CONNECTION_DIST * 1.25) {
        continue;
      }

      const x = from.x + (to.x - from.x) * pulse.progress;
      const y = from.y + (to.y - from.y) * pulse.progress;
      const alpha = 0.85 * (1 - pulse.progress * 0.5);
      const gradient = context.createRadialGradient(x, y, 0, x, y, 8);
      gradient.addColorStop(0, pulse.color);
      gradient.addColorStop(0.45, pulse.color);
      gradient.addColorStop(1, 'transparent');
      context.globalAlpha = alpha;
      context.fillStyle = gradient;
      context.beginPath();
      context.arc(x, y, 8, 0, Math.PI * 2);
      context.fill();
      context.globalAlpha = 1;
    }

    pulses = alive;
  }

  function drawNodes(context: CanvasRenderingContext2D, time: number): void {
    for (const node of nodes) {
      drawNodeGlow(context, node, time);
      context.globalAlpha = Math.max(0.32, 0.55 + Math.sin(time * 0.0009 + node.phase) * 0.2);
      context.fillStyle = node.color;
      context.beginPath();
      context.arc(node.x, node.y, node.r, 0, Math.PI * 2);
      context.fill();
      context.globalAlpha = 1;
    }
  }

  function drawNodeGlow(context: CanvasRenderingContext2D, node: CanvasNode, time: number): void {
    if (node.glowUntil <= time) {
      return;
    }

    const meetProgress = (node.glowUntil - time) / MEET_GLOW_MS;
    const glowRadius = 9 + (1 - meetProgress) * 12;
    const gradient = context.createRadialGradient(node.x, node.y, 0, node.x, node.y, glowRadius);
    gradient.addColorStop(0, node.color);
    gradient.addColorStop(0.35, node.color);
    gradient.addColorStop(1, 'transparent');
    context.globalAlpha = 0.45 * meetProgress;
    context.fillStyle = gradient;
    context.beginPath();
    context.arc(node.x, node.y, glowRadius, 0, Math.PI * 2);
    context.fill();
    context.globalAlpha = 1;
  }

  function distanceBetween(first: CanvasNode, second: CanvasNode): number {
    const dx = first.x - second.x;
    const dy = first.y - second.y;

    return Math.sqrt(dx * dx + dy * dy);
  }

  onMounted(() => {
    if (!window.matchMedia) {
      return;
    }

    desktopQuery = window.matchMedia('(min-width: 1024px)');
    reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    addQueryListener(desktopQuery, syncCanvasAnimation);
    addQueryListener(reducedMotionQuery, syncCanvasAnimation);

    syncCanvasAnimation();
  });

  onBeforeUnmount(() => {
    removeQueryListener(desktopQuery, syncCanvasAnimation);
    removeQueryListener(reducedMotionQuery, syncCanvasAnimation);
    stopCanvasAnimation();
  });

  return {
    canvasRef,
    sparks,
  };
}
