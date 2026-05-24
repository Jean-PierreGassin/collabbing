<script setup lang="ts">
import { CircleAlert, Info, TriangleAlert, X } from '@lucide/vue';
import { InfoTooltip } from '@/components/ui/tooltip';
import { useFormFieldControl } from '@/composables/useFormFieldControl';
import type { FieldValidator } from '@/lib/formValidation';

const props = withDefaults(defineProps<{
  errorKey?: string;
  externalErrors?: string[];
  id: string;
  label: string;
  hideLabel?: boolean;
  help?: string;
  tooltip?: string;
  validator?: FieldValidator;
}>(), {
  errorKey: undefined,
  externalErrors: () => [],
  hideLabel: false,
  help: undefined,
  tooltip: undefined,
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

const {
  canClear,
  clearButtonLabel,
  clearButtonStyle,
  clearControl,
  describedBy,
  errorId,
  errors,
  feedbackClass,
  feedbackId,
  field,
  helpId,
  invalid,
  labelClass,
  showSpark,
  sparkKey,
  sparkStyle,
  valid,
  validationMessage,
} = useFormFieldControl(props);
</script>

<template>
  <div
    ref="field"
    class="relative flex flex-col gap-2">
    <div class="flex items-center gap-1.5">
      <label
        :for="id"
        :class="labelClass">{{ label }}</label>
      <InfoTooltip
        v-if="tooltip && !hideLabel"
        :id="`${id}-tooltip`"
        :label="tooltip"
        align="start" />
    </div>
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
      role="status">
      Looks good.
    </p>
  </div>
</template>
