import { nextTick, ref } from 'vue';
import { router } from '@inertiajs/vue3';

export type AuthMorphPhase = 'idle' | 'collapsing' | 'checking' | 'filling' | 'leaving';

type FieldErrors = Record<string, string[]>;

interface AuthMorphCardOptions {
  action: string;
  successHref: string;
}

function wait(milliseconds: number): Promise<void> {
  return new Promise((resolve) => {
    window.setTimeout(resolve, milliseconds);
  });
}

function animationFrame(): Promise<void> {
  return new Promise((resolve) => {
    if (window.requestAnimationFrame) {
      window.requestAnimationFrame(() => resolve());

      return;
    }

    window.setTimeout(resolve, 16);
  });
}

function prefersReducedMotion(): boolean {
  return window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;
}

export function useAuthMorphCard(options: AuthMorphCardOptions) {
  const shell = ref<HTMLElement | null>(null);
  const fieldErrors = ref<FieldErrors>({});
  const formError = ref<string | undefined>(undefined);
  const isSubmitting = ref(false);
  const phase = ref<AuthMorphPhase>('idle');
  const shouldRenderForm = ref(true);
  const shellStyle = ref<Record<string, string>>({});

  let measuredHeight = 64;

  function errorsFor(field: string): string[] {
    return fieldErrors.value[field] ?? [];
  }

  function clearFieldError(event: Event): void {
    const target = event.target;

    if (!(target instanceof HTMLInputElement || target instanceof HTMLSelectElement || target instanceof HTMLTextAreaElement)) {
      return;
    }

    if (!target.name || !fieldErrors.value[target.name]) {
      return;
    }

    const nextErrors = { ...fieldErrors.value };

    delete nextErrors[target.name];
    fieldErrors.value = nextErrors;
  }

  function setMeasuredShellSize(): void {
    const bounds = shell.value?.getBoundingClientRect();
    measuredHeight = Math.max(bounds?.height ?? 0, 64);

    shellStyle.value = {
      height: `${measuredHeight}px`,
      translate: '0 0',
      width: `${Math.max(bounds?.width ?? 0, 64)}px`,
    };
  }

  async function playCompletion(): Promise<void> {
    setMeasuredShellSize();
    shouldRenderForm.value = false;
    phase.value = 'collapsing';
    await nextTick();
    await animationFrame();

    shellStyle.value = {
      height: '4rem',
      translate: `0 ${Math.max(0, (measuredHeight - 64) / 2)}px`,
      width: '4rem',
    };

    if (prefersReducedMotion()) {
      phase.value = 'filling';
      await wait(120);
      phase.value = 'leaving';
      await wait(80);

      return;
    }

    await wait(520);
    phase.value = 'checking';
    await wait(860);
    phase.value = 'filling';
    await wait(760);
    phase.value = 'leaving';
    await wait(220);
  }

  async function parseErrorResponse(response: Response): Promise<void> {
    let payload: {
      errors?: Record<string, string[] | string>;
      message?: string;
    };

    try {
      payload = await response.json();
    } catch {
      payload = {};
    }

    const nextErrors: FieldErrors = {};

    for (const [
      field,
      messages,
    ] of Object.entries(payload.errors ?? {})) {
      if (Array.isArray(messages)) {
        nextErrors[field] = messages;
      } else {
        nextErrors[field] = [messages];
      }
    }

    fieldErrors.value = nextErrors;
    formError.value = Object.keys(nextErrors).length === 0
      ? payload.message ?? 'Something went wrong. Please try again.'
      : undefined;
  }

  async function focusFirstErroredField(form: HTMLFormElement): Promise<void> {
    await nextTick();

    const firstField = Object.keys(fieldErrors.value)[0];
    const control = firstField ? form.elements.namedItem(firstField) : null;

    if (control instanceof HTMLElement) {
      control.focus();
    }
  }

  async function submitForm(event: SubmitEvent): Promise<void> {
    if (event.defaultPrevented || phase.value !== 'idle') {
      return;
    }

    const form = event.currentTarget;

    if (!(form instanceof HTMLFormElement)) {
      return;
    }

    if (!form.checkValidity()) {
      form.reportValidity();

      return;
    }

    event.preventDefault();
    fieldErrors.value = {};
    formError.value = undefined;
    isSubmitting.value = true;

    try {
      const response = await fetch(options.action, {
        body: new FormData(form),
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        method: 'POST',
      });

      if (response.ok) {
        await playCompletion();
        router.visit(options.successHref);

        return;
      }

      await parseErrorResponse(response);
      await focusFirstErroredField(form);
    } catch {
      formError.value = 'Something went wrong. Please try again.';
    } finally {
      if (phase.value === 'idle') {
        isSubmitting.value = false;
      }
    }
  }

  return {
    clearFieldError,
    errorsFor,
    formError,
    isSubmitting,
    phase,
    shell,
    shellStyle,
    shouldRenderForm,
    submitForm,
  };
}
