<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { CircleAlert, Info, TriangleAlert, X } from '@lucide/vue';
import { fieldErrors } from '@/lib/forms';
import type { FieldValidator, FormControlElement } from '@/lib/formValidation';

const props = withDefaults(defineProps<{
  errorKey?: string;
  externalErrors?: string[];
  id: string;
  label: string;
  hideLabel?: boolean;
  help?: string;
  validator?: FieldValidator;
}>(), {
  errorKey: undefined,
  externalErrors: () => [],
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
const hasChanged = ref(false);
const sparkKey = ref(0);
const showSpark = ref(false);
const sparkStyle = ref<Record<string, string>>({});
const clearButtonStyle = ref<Record<string, string>>({});
const hasValue = ref(false);
const isClearable = ref(false);
let sparkTimer: number | undefined;
const cleanupCallbacks: (() => void)[] = [];

const errors = computed(() => [
  ...fieldErrors(props.errorKey ?? props.id),
  ...props.externalErrors,
]);
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
  let className = 'form-control-feedback';

  if (isClearable.value) {
    className = `${className} form-control-clearable`;
  }

  if (invalid.value) {
    return `${className} form-control-feedback-invalid`;
  }

  if (valid.value) {
    return `${className} form-control-feedback-valid`;
  }

  return className;
});
const canClear = computed(() => isClearable.value && hasValue.value);
const clearButtonLabel = computed(() => `Clear ${props.label.toLowerCase()}`);
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

function isControlClearable(nextControl: FormControlElement): boolean {
  if (nextControl.disabled) {
    return false;
  }

  if (nextControl instanceof HTMLTextAreaElement) {
    return !nextControl.readOnly;
  }

  if (!(nextControl instanceof HTMLInputElement)) {
    return false;
  }

  if (nextControl.readOnly) {
    return false;
  }

  return [
    'email',
    'password',
    'search',
    'tel',
    'text',
    'url',
  ].includes(nextControl.type);
}

function syncValueState(): void {
  hasValue.value = (control.value?.value ?? '').length > 0;
}

function nativeValidationMessage(nextControl: FormControlElement, enforceRequired: boolean): string | undefined {
  const validity = nextControl.validity;

  if (validity.valid) {
    return undefined;
  }

  if (validity.valueMissing) {
    if (!enforceRequired) {
      return undefined;
    }

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

function shouldSkipValidator(nextControl: FormControlElement, enforceRequired: boolean): boolean {
  return !enforceRequired && nextControl.validity.valueMissing;
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
    height: `${controlBounds.height}px`,
    left: `${controlBounds.left - fieldBounds.left}px`,
    top: `${controlBounds.top - fieldBounds.top}px`,
    width: `${controlBounds.width}px`,
  };
}

function positionClearButton(): void {
  if (!field.value || !control.value) {
    clearButtonStyle.value = {};

    return;
  }

  const fieldBounds = field.value.getBoundingClientRect();
  const controlBounds = control.value.getBoundingClientRect();
  const buttonSize = 24;
  const textareaScrollbarWidth = control.value instanceof HTMLTextAreaElement
    ? Math.max(0, control.value.offsetWidth - control.value.clientWidth)
    : 0;
  const rightInset = 8 + textareaScrollbarWidth;
  const topInset = control.value instanceof HTMLTextAreaElement ? 10 : ((controlBounds.height - buttonSize) / 2);

  clearButtonStyle.value = {
    left: `${controlBounds.right - fieldBounds.left - rightInset - buttonSize}px`,
    top: `${controlBounds.top - fieldBounds.top + topInset}px`,
  };
}

function validateControl(reveal: boolean, animate: boolean, enforceRequired = false): boolean {
  const nextControl = control.value;

  if (!nextControl) {
    return true;
  }

  if (reveal) {
    hasInteracted.value = true;
  }

  syncValueState();
  positionClearButton();
  nextControl.setCustomValidity('');

  let message = nativeValidationMessage(nextControl, enforceRequired);

  if (!message && props.validator && !shouldSkipValidator(nextControl, enforceRequired)) {
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

  if (!hasChanged.value) {
    feedbackState.value = 'idle';
    validationMessage.value = undefined;

    return true;
  }

  feedbackState.value = 'valid';

  if (animate && previousState !== 'valid') {
    setSpark();
  }

  return true;
}

function handleInput(): void {
  hasChanged.value = true;
  syncValueState();
  positionClearButton();
  validateControl(true, true);
}

function handleBlur(): void {
  validateControl(true, true);
}

function handleInvalid(): void {
  validateControl(true, false, true);
}

function handleKeydown(event: Event): void {
  if (!(event instanceof KeyboardEvent) || event.key !== 'Escape' || !canClear.value) {
    return;
  }

  event.preventDefault();
  clearControl();
}

function handleFormInput(event: Event): void {
  if (!hasInteracted.value || event.target === control.value) {
    return;
  }

  validateControl(false, false);
}

function clearControl(): void {
  const nextControl = control.value;

  if (!nextControl || nextControl.disabled) {
    return;
  }

  if (
    (nextControl instanceof HTMLInputElement || nextControl instanceof HTMLTextAreaElement)
    && nextControl.readOnly
  ) {
    return;
  }

  nextControl.value = '';
  hasChanged.value = true;
  nextControl.dispatchEvent(new Event('input', { bubbles: true }));
  nextControl.dispatchEvent(new Event('change', { bubbles: true }));
  nextControl.focus();
  syncValueState();
  validateControl(true, false);
}

function handleFormSubmit(event: Event): void {
  if (validateControl(true, false, true)) {
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
  addListener(control.value, 'keydown', handleKeydown);
  isClearable.value = isControlClearable(control.value);
  syncValueState();
  positionClearButton();

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
  <div
    ref="field"
    class="relative flex flex-col gap-2">
    <label
      :for="id"
      :class="labelClass">{{ label }}</label>
    <slot
      :invalid="invalid"
      :valid="valid"
      :described-by="describedBy"
      :feedback-class="feedbackClass" />
    <span
      v-if="showSpark"
      :key="sparkKey"
      class="form-field-sparks pointer-events-none absolute z-10"
      :style="sparkStyle"
      aria-hidden="true"
    >
      <span />
      <span />
      <span />
      <span />
      <span />
      <span />
      <span />
      <span />
    </span>
    <button
      v-if="canClear"
      type="button"
      class="form-field-clear-button absolute z-20 inline-flex size-6 items-center justify-center rounded-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
      :style="clearButtonStyle"
      :aria-label="clearButtonLabel"
      tabindex="-1"
      @click="clearControl"
    >
      <X
        class="size-4"
        aria-hidden="true" />
    </button>
    <div
      v-if="help || validationMessage || errors.length > 0"
      class="flex flex-col gap-1">
      <p
        v-if="help"
        :id="helpId"
        class="flex gap-2 text-sm leading-5 text-muted-foreground">
        <Info
          class="mt-0.5 size-4 shrink-0 text-muted-foreground"
          aria-hidden="true" />
        <span>{{ help }}</span>
      </p>
      <p
        v-if="validationMessage"
        :id="feedbackId"
        class="flex gap-2 text-sm leading-5 text-destructive"
        role="alert">
        <TriangleAlert
          class="mt-0.5 size-4 shrink-0"
          aria-hidden="true" />
        <span>{{ validationMessage }}</span>
      </p>
      <p
        v-if="errors.length > 0"
        :id="errorId"
        class="flex gap-2 text-sm leading-5 text-destructive"
        role="alert">
        <CircleAlert
          class="mt-0.5 size-4 shrink-0"
          aria-hidden="true" />
        <span>
          <span
            v-for="error in errors"
            :key="error"
            class="block">{{ error }}</span>
        </span>
      </p>
    </div>
    <p
      v-else-if="valid"
      :id="feedbackId"
      class="sr-only"
      role="status">Looks good.</p>
  </div>
</template>
