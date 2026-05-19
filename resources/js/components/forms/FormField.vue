<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { fieldErrors } from '@/lib/forms';
import type { FieldValidator, FormControlElement } from '@/lib/formValidation';

const props = withDefaults(defineProps<{
  errorKey?: string;
  id: string;
  label: string;
  hideLabel?: boolean;
  help?: string;
  validator?: FieldValidator;
}>(), {
  errorKey: undefined,
  hideLabel: false,
  help: undefined,
  validator: undefined,
});

defineSlots<{
  default(props: {
    describedBy: string | undefined;
    feedbackClass: string;
    invalid: boolean;
    valid: boolean;
  }): unknown;
}>();

const field = ref<HTMLElement | null>(null);
const control = ref<FormControlElement | null>(null);
const hasInteracted = ref(false);
const feedbackState = ref<'idle' | 'invalid' | 'valid'>('idle');
const validationMessage = ref<string | undefined>(undefined);
const sparkKey = ref(0);
const showSpark = ref(false);
const sparkStyle = ref<Record<string, string>>({});
let sparkTimer: number | undefined;
const cleanupCallbacks: (() => void)[] = [];

const errors = computed(() => fieldErrors(props.errorKey ?? props.id));
const helpId = computed(() => {
  if (props.help) {
    return `${props.id}-help`;
  }

  return undefined;
});
const errorId = computed(() => {
  if (errors.value.length > 0) {
    return `${props.id}-error`;
  }

  return undefined;
});
const feedbackId = computed(() => {
  if (!validationMessage.value && feedbackState.value !== 'valid') {
    return undefined;
  }

  return `${props.id}-feedback`;
});
const serverInvalid = computed(() => errors.value.length > 0);
const invalid = computed(() => serverInvalid.value || feedbackState.value === 'invalid');
const valid = computed(() => !serverInvalid.value && feedbackState.value === 'valid');
const describedBy = computed(() => [helpId.value, feedbackId.value, errorId.value].filter(Boolean).join(' ') || undefined);
const feedbackClass = computed(() => {
  if (invalid.value) {
    return 'form-control-feedback form-control-feedback-invalid';
  }

  if (valid.value) {
    return 'form-control-feedback form-control-feedback-valid';
  }

  return 'form-control-feedback';
});
const labelClass = computed(() => {
  if (props.hideLabel) {
    return 'sr-only';
  }

  return 'text-sm font-medium text-foreground';
});

function addListener(target: EventTarget, event: string, handler: (event: Event) => void, useCapture = false): void {
  target.addEventListener(event, handler, useCapture);
  cleanupCallbacks.push(() => target.removeEventListener(event, handler, useCapture));
}

function findControl(): FormControlElement | null {
  const candidate = field.value?.querySelector(`#${props.id}`);

  if (
    candidate instanceof HTMLInputElement
    || candidate instanceof HTMLSelectElement
    || candidate instanceof HTMLTextAreaElement
  ) {
    return candidate;
  }

  return null;
}

function nativeValidationMessage(nextControl: FormControlElement): string | undefined {
  const validity = nextControl.validity;

  if (validity.valid) {
    return undefined;
  }

  if (validity.valueMissing) {
    return `${props.label} is required.`;
  }

  if (validity.typeMismatch && nextControl instanceof HTMLInputElement && nextControl.type === 'email') {
    return 'Enter a valid email address.';
  }

  if (validity.tooShort && 'minLength' in nextControl) {
    return `Use at least ${nextControl.minLength.toLocaleString()} characters.`;
  }

  if (validity.tooLong && 'maxLength' in nextControl) {
    return `Use ${nextControl.maxLength.toLocaleString()} characters or fewer.`;
  }

  if (validity.patternMismatch) {
    return 'Use the requested format.';
  }

  return nextControl.validationMessage || 'Check this field.';
}

function setSpark(): void {
  positionSpark();
  window.clearTimeout(sparkTimer);
  sparkKey.value += 1;
  showSpark.value = true;
  sparkTimer = window.setTimeout(() => {
    showSpark.value = false;
  }, 700);
}

function positionSpark(): void {
  if (!field.value || !control.value) {
    sparkStyle.value = {};

    return;
  }

  const fieldBounds = field.value.getBoundingClientRect();
  const controlBounds = control.value.getBoundingClientRect();

  sparkStyle.value = {
    left: `${controlBounds.right - fieldBounds.left - 22}px`,
    top: `${controlBounds.top - fieldBounds.top + (controlBounds.height / 2)}px`,
  };
}

function validateControl(reveal: boolean, animate: boolean): boolean {
  const nextControl = control.value;

  if (!nextControl) {
    return true;
  }

  if (reveal) {
    hasInteracted.value = true;
  }

  nextControl.setCustomValidity('');

  let message = nativeValidationMessage(nextControl);

  if (!message && props.validator) {
    message = props.validator(nextControl.value, {
      control: nextControl,
      form: nextControl.form,
    });
  }

  nextControl.setCustomValidity(message ?? '');

  if (!hasInteracted.value) {
    feedbackState.value = 'idle';
    validationMessage.value = undefined;

    return !message;
  }

  const previousState = feedbackState.value;
  validationMessage.value = message;

  if (message) {
    feedbackState.value = 'invalid';

    return false;
  }

  if (nextControl.value.trim() === '') {
    feedbackState.value = 'idle';

    return true;
  }

  feedbackState.value = 'valid';

  if (animate && previousState !== 'valid') {
    setSpark();
  }

  return true;
}

function handleInput(): void {
  validateControl(true, true);
}

function handleBlur(): void {
  validateControl(true, true);
}

function handleInvalid(): void {
  validateControl(true, false);
}

function handleFormInput(event: Event): void {
  if (!hasInteracted.value || event.target === control.value) {
    return;
  }

  validateControl(false, false);
}

function handleFormSubmit(event: Event): void {
  if (validateControl(true, false)) {
    return;
  }

  event.preventDefault();
  control.value?.reportValidity();
}

function wireControl(): void {
  control.value = findControl();

  if (!control.value) {
    return;
  }

  addListener(control.value, 'input', handleInput);
  addListener(control.value, 'change', handleInput);
  addListener(control.value, 'blur', handleBlur);
  addListener(control.value, 'invalid', handleInvalid);

  if (control.value.form) {
    addListener(control.value.form, 'input', handleFormInput);
    addListener(control.value.form, 'submit', handleFormSubmit, true);
  }
}

onMounted(() => {
  void nextTick(wireControl);
});

onBeforeUnmount(() => {
  window.clearTimeout(sparkTimer);

  for (const cleanup of cleanupCallbacks) {
    cleanup();
  }
});

watch(errors, () => {
  if (serverInvalid.value) {
    feedbackState.value = 'invalid';

    return;
  }

  validateControl(false, false);
});
</script>

<template>
  <div ref="field" class="relative flex flex-col gap-2">
    <label :for="id" :class="labelClass">{{ label }}</label>
    <slot :invalid="invalid" :valid="valid" :described-by="describedBy" :feedback-class="feedbackClass" />
    <span
      v-if="showSpark"
      :key="sparkKey"
      class="form-field-sparks pointer-events-none absolute z-10 size-6 -translate-y-1/2"
      :style="sparkStyle"
      aria-hidden="true"
    >
      <span />
      <span />
      <span />
    </span>
    <p v-if="help" :id="helpId" class="text-sm text-muted-foreground">{{ help }}</p>
    <p v-if="validationMessage" :id="feedbackId" class="text-sm text-destructive" role="alert">{{ validationMessage }}</p>
    <p v-else-if="valid" :id="feedbackId" class="sr-only" role="status">Looks good.</p>
    <p v-if="errors.length > 0" :id="errorId" class="text-sm text-destructive" role="alert">
      <span v-for="error in errors" :key="error" class="block">{{ error }}</span>
    </p>
  </div>
</template>
