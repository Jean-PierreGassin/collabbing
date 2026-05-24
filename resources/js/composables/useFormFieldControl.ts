import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { fieldErrors } from '@/lib/forms';
import type { FieldValidator, FormControlElement } from '@/lib/formValidation';

export interface FormFieldControlOptions {
  errorKey?: string;
  externalErrors?: string[];
  help?: string;
  hideLabel?: boolean;
  id: string;
  label: string;
  validator?: FieldValidator;
}

export function useFormFieldControl(options: FormFieldControlOptions) {
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
  const cleanupCallbacks: (() => void)[] = [];

  let sparkTimer: number | undefined;

  const errors = computed(() => [
    ...fieldErrors(options.errorKey ?? options.id),
    ...(options.externalErrors ?? []),
  ]);

  const helpId = computed(() => {
    if (options.help) {
      return `${options.id}-help`;
    }

    return undefined;
  });

  const errorId = computed(() => {
    if (errors.value.length > 0) {
      return `${options.id}-error`;
    }

    return undefined;
  });

  const feedbackId = computed(() => {
    if (!validationMessage.value && feedbackState.value !== 'valid') {
      return undefined;
    }

    return `${options.id}-feedback`;
  });

  const serverInvalid = computed(() => errors.value.length > 0);
  const invalid = computed(() => serverInvalid.value || feedbackState.value === 'invalid');
  const valid = computed(() => !serverInvalid.value && feedbackState.value === 'valid');

  const describedBy = computed(() => [
    helpId.value,
    feedbackId.value,
    errorId.value,
  ].filter(Boolean).join(' ') || undefined);

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
  const clearButtonLabel = computed(() => `Clear ${options.label.toLowerCase()}`);

  const labelClass = computed(() => {
    if (options.hideLabel) {
      return 'sr-only';
    }

    return 'text-sm font-medium text-foreground';
  });

  function addListener(target: EventTarget, event: string, handler: (event: Event) => void, useCapture = false): void {
    target.addEventListener(event, handler, useCapture);
    cleanupCallbacks.push(() => target.removeEventListener(event, handler, useCapture));
  }

  function findControl(): FormControlElement | null {
    const candidate = field.value?.querySelector(`#${options.id}`);

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

      return `${options.label} is required.`;
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
    if (nextControl.value.trim() !== '') {
      return false;
    }

    if (! nextControl.required) {
      return true;
    }

    return ! enforceRequired && nextControl.validity.valueMissing;
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

    if (!message && options.validator && !shouldSkipValidator(nextControl, enforceRequired)) {
      message = options.validator(nextControl.value, {
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

  return {
    canClear,
    clearButtonLabel,
    clearButtonStyle,
    clearControl,
    describedBy,
    errorId,
    errors,
    field,
    feedbackClass,
    feedbackId,
    helpId,
    invalid,
    labelClass,
    showSpark,
    sparkKey,
    sparkStyle,
    valid,
    validationMessage,
  };
}
