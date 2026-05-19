<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { AlertTriangle, CheckCircle2, Sparkles, X } from '@lucide/vue';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();
const errors = computed(() => session.flash.errors);

type ToastTone = 'status' | 'error';

interface ToastMessage {
  id: number;
  message: string;
  tone: ToastTone;
  timer: number | null;
}

const toastDuration = 5000;
const toasts = ref<ToastMessage[]>([]);
const flashSignature = computed(() => JSON.stringify({
  status: session.flash.status,
  errors: errors.value,
}));

let nextToastId = 1;

function dismissToast(id: number): void {
  const toast = toasts.value.find((message) => message.id === id);

  if (toast?.timer) {
    window.clearTimeout(toast.timer);
  }

  toasts.value = toasts.value.filter((message) => message.id !== id);
}

function startTimer(toast: ToastMessage): void {
  toast.timer = window.setTimeout(() => dismissToast(toast.id), toastDuration);
}

function pauseToast(toast: ToastMessage): void {
  if (!toast.timer) {
    return;
  }

  window.clearTimeout(toast.timer);
  toast.timer = null;
}

function resumeToast(toast: ToastMessage): void {
  if (!toast.timer) {
    startTimer(toast);
  }
}

function addToast(message: string, tone: ToastTone): void {
  toasts.value.forEach((activeToast) => {
    if (activeToast.timer) {
      window.clearTimeout(activeToast.timer);
    }
  });

  const toast: ToastMessage = {
    id: nextToastId++,
    message,
    tone,
    timer: null,
  };

  toasts.value = [toast];
  startTimer(toast);
}

function toastTitle(tone: ToastTone): string {
  if (tone === 'error') {
    return 'Needs attention';
  }

  return 'Success';
}

function toastClass(tone: ToastTone): string {
  if (tone === 'error') {
    return 'border-destructive/35 text-foreground';
  }

  return 'border-primary/35 text-foreground';
}

function toastAccentClass(tone: ToastTone): string {
  if (tone === 'error') {
    return 'bg-destructive';
  }

  return 'bg-primary';
}

function toastRole(tone: ToastTone): 'alert' | 'status' {
  if (tone === 'error') {
    return 'alert';
  }

  return 'status';
}

function toastAriaLive(tone: ToastTone): 'assertive' | 'polite' {
  if (tone === 'error') {
    return 'assertive';
  }

  return 'polite';
}

function toastIconClass(tone: ToastTone): string {
  if (tone === 'error') {
    return 'bg-destructive/12 text-destructive ring-destructive/30';
  }

  return 'bg-primary/12 text-primary ring-primary/30';
}

watch(flashSignature, () => {
  if (session.flash.status) {
    addToast(session.flash.status, 'status');
  }

  if (errors.value.length === 1) {
    addToast(errors.value[0], 'error');
  } else if (errors.value.length > 1) {
    addToast(`${errors.value.length} fields need attention. Review the highlighted fields.`, 'error');
  }
}, { immediate: true });

onBeforeUnmount(() => {
  toasts.value.forEach((toast) => {
    if (toast.timer) {
      window.clearTimeout(toast.timer);
    }
  });
});
</script>

<template>
  <TransitionGroup
    tag="div"
    name="toast-slide"
    class="pointer-events-none fixed inset-x-3 bottom-3 z-50 flex flex-col gap-2 sm:inset-x-auto sm:bottom-6 sm:right-6 sm:w-[calc(100vw-2rem)] sm:max-w-[27rem] sm:gap-3"
    aria-label="Notifications"
  >
    <div
      v-for="toast in toasts"
      :key="toast.id"
      class="pointer-events-auto relative grid min-h-24 grid-cols-[auto_1fr_auto] items-start gap-3 overflow-hidden rounded-lg border bg-zinc-950/95 px-4 py-3 text-sm shadow-[0_22px_60px_rgba(0,0,0,0.42)] ring-1 ring-white/10 backdrop-blur-md sm:gap-4 sm:px-5 sm:py-4"
      :class="toastClass(toast.tone)"
      :role="toastRole(toast.tone)"
      :aria-live="toastAriaLive(toast.tone)"
      @mouseenter="pauseToast(toast)"
      @mouseleave="resumeToast(toast)"
      @focusin="pauseToast(toast)"
      @focusout="resumeToast(toast)"
    >
      <span
        class="absolute inset-y-3 left-0 w-1 rounded-r-full"
        :class="toastAccentClass(toast.tone)"
        aria-hidden="true"
      />
      <div
        class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full ring-1"
        :class="toastIconClass(toast.tone)"
      >
        <AlertTriangle v-if="toast.tone === 'error'" class="size-4" aria-hidden="true" />
        <CheckCircle2 v-else class="size-4" aria-hidden="true" />
      </div>
      <div class="min-w-0 space-y-1">
        <div class="flex min-w-0 items-center gap-2">
          <Sparkles class="size-3.5 shrink-0 text-muted-foreground/80" aria-hidden="true" />
          <p class="truncate font-semibold leading-5 text-white">{{ toastTitle(toast.tone) }}</p>
        </div>
        <p class="line-clamp-3 leading-5 text-muted-foreground sm:line-clamp-none">{{ toast.message }}</p>
      </div>
      <button
        type="button"
        class="-mr-1 -mt-1 flex size-9 shrink-0 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-white/10 hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
        aria-label="Dismiss notification"
        @click="dismissToast(toast.id)"
      >
        <X class="size-4" aria-hidden="true" />
      </button>
    </div>
  </TransitionGroup>
</template>
