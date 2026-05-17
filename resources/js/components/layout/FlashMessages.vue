<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { AlertTriangle, CheckCircle2, X } from '@lucide/vue';
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
  return tone === 'error' ? 'Needs attention' : 'Saved';
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
    class="pointer-events-none fixed inset-x-3 bottom-3 z-50 flex flex-col gap-2 sm:inset-x-auto sm:bottom-6 sm:right-6 sm:w-[calc(100vw-2rem)] sm:max-w-md sm:gap-3"
    aria-label="Notifications"
  >
    <div
      v-for="toast in toasts"
      :key="toast.id"
      class="pointer-events-auto grid grid-cols-[1fr_auto] items-center gap-3 rounded-md border bg-popover px-4 py-3 text-sm shadow-2xl shadow-black/35 ring-1 ring-white/10 sm:min-h-24 sm:grid-cols-[auto_1fr_auto] sm:items-start sm:gap-4 sm:p-5 sm:text-base"
      :class="toast.tone === 'error' ? 'border-destructive/70 text-foreground' : 'border-primary/70 text-foreground'"
      :role="toast.tone === 'error' ? 'alert' : 'status'"
      :aria-live="toast.tone === 'error' ? 'assertive' : 'polite'"
      @mouseenter="pauseToast(toast)"
      @mouseleave="resumeToast(toast)"
      @focusin="pauseToast(toast)"
      @focusout="resumeToast(toast)"
    >
      <div
        class="hidden size-10 shrink-0 items-center justify-center rounded-md sm:flex"
        :class="toast.tone === 'error' ? 'bg-destructive/15 text-destructive' : 'bg-primary/15 text-primary'"
      >
        <AlertTriangle v-if="toast.tone === 'error'" class="size-5" aria-hidden="true" />
        <CheckCircle2 v-else class="size-5" aria-hidden="true" />
      </div>
      <div class="min-w-0">
        <p class="sr-only font-semibold text-white sm:not-sr-only">{{ toastTitle(toast.tone) }}</p>
        <p class="line-clamp-2 leading-5 text-foreground sm:mt-1 sm:line-clamp-none sm:leading-6 sm:text-muted-foreground">{{ toast.message }}</p>
      </div>
      <button
        type="button"
        class="-mr-2 flex size-10 shrink-0 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50 sm:-mr-1 sm:-mt-1 sm:size-auto sm:p-1.5"
        aria-label="Dismiss notification"
        @click="dismissToast(toast.id)"
      >
        <X class="size-4" aria-hidden="true" />
      </button>
    </div>
  </TransitionGroup>
</template>
