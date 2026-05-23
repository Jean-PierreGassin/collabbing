import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { useSessionStore } from '@/stores/session';

type ToastTone = 'status' | 'error';

interface ToastMessage {
  id: number;
  message: string;
  tone: ToastTone;
  timer: number | null;
}

const toastDuration = 5000;

export function useFlashToasts() {
  const session = useSessionStore();
  const errors = computed(() => session.flash.errors);
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

  return {
    dismissToast,
    pauseToast,
    resumeToast,
    toastAccentClass,
    toastAriaLive,
    toastIconClass,
    toastRole,
    toasts,
    toastTitle,
  };
}
