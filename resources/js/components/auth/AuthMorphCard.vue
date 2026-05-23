<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { CircleAlert } from '@lucide/vue';

type AuthMorphPhase = 'idle' | 'collapsing' | 'checking' | 'filling' | 'leaving';

type FieldErrors = Record<string, string[]>;

const props = withDefaults(defineProps<{
  action: string;
  maxWidth?: 'md' | 'lg';
  successHref: string;
  title: string;
}>(), {
  maxWidth: 'md',
});

defineSlots<{
  default(props: {
    errorsFor: (field: string) => string[];
    isSubmitting: boolean;
    phase: AuthMorphPhase;
  }): unknown;
}>();

const shell = ref<HTMLElement | null>(null);
const fieldErrors = ref<FieldErrors>({});
const formError = ref<string | undefined>(undefined);
const isSubmitting = ref(false);
const phase = ref<AuthMorphPhase>('idle');
const shouldRenderForm = ref(true);
const shellStyle = ref<Record<string, string>>({});
let measuredHeight = 64;

const widthClass = computed(() => {
  if (props.maxWidth === 'lg') {
    return 'max-w-2xl';
  }

  return 'max-w-md';
});
const isAnimating = computed(() => phase.value !== 'idle');
const isFilled = computed(() => phase.value === 'filling' || phase.value === 'leaving');

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

  for (const [field, messages] of Object.entries(payload.errors ?? {})) {
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
    const response = await fetch(props.action, {
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
      router.visit(props.successHref);

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
</script>

<template>
  <div
    class="mx-auto w-full"
    :class="widthClass">
    <div
      ref="shell"
      class="auth-morph-card relative mx-auto overflow-hidden rounded-lg border border-border bg-card text-card-foreground shadow-sm shadow-black/10"
      :class="{
        'auth-morph-card-active': isAnimating,
        'auth-morph-card-fill': isFilled,
        'auth-morph-card-leaving': phase === 'leaving',
      }"
      :style="shellStyle"
    >
      <div
        v-if="shouldRenderForm"
        class="auth-morph-content"
        :class="{ 'auth-morph-content-hidden': isAnimating }"
        :aria-hidden="isAnimating || undefined"
      >
        <div class="flex flex-col gap-1.5 border-b border-border p-6">
          <h1 class="text-2xl font-semibold text-white">{{ title }}</h1>
        </div>
        <form
          method="POST"
          :action="action"
          class="flex flex-col gap-4 p-6"
          :aria-busy="isSubmitting || undefined"
          @input="clearFieldError"
          @submit="submitForm"
        >
          <p
            v-if="formError"
            class="flex gap-2 rounded-md bg-destructive/12 px-3 py-2 text-sm leading-5 text-destructive"
            role="alert">
            <CircleAlert
              class="mt-0.5 size-4 shrink-0"
              aria-hidden="true" />
            <span>{{ formError }}</span>
          </p>
          <slot
            :errors-for="errorsFor"
            :is-submitting="isSubmitting"
            :phase="phase" />
        </form>
      </div>

      <div
        v-if="isAnimating"
        class="auth-success-stage"
        :class="{
          'auth-success-stage-visible': phase === 'checking' || isFilled,
          'auth-success-stage-fill': isFilled,
        }"
        role="status"
        aria-live="polite"
      >
        <svg
          class="auth-success-svg"
          viewBox="0 0 64 64"
          aria-hidden="true">
          <circle
            class="auth-success-ring"
            cx="32"
            cy="32"
            r="22" />
          <path
            class="auth-success-check"
            d="M20 33.5 28.5 42 45 23" />
        </svg>
        <span class="sr-only">Success. Taking you to your ideas.</span>
        <span
          v-if="isFilled"
          class="auth-success-sparks"
          aria-hidden="true">
          <span />
          <span />
          <span />
          <span />
          <span />
          <span />
          <span />
          <span />
          <span />
          <span />
          <span />
          <span />
        </span>
      </div>
    </div>
  </div>
</template>
