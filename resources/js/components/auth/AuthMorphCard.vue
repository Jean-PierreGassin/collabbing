<script setup lang="ts">
import { computed } from 'vue';
import { CircleAlert } from '@lucide/vue';
import { useAuthMorphCard } from '@/composables/useAuthMorphCard';
import type { AuthMorphPhase } from '@/composables/useAuthMorphCard';

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

const {
  clearFieldError,
  errorsFor,
  formError,
  isSubmitting,
  phase,
  shell,
  shellStyle,
  shouldRenderForm,
  submitForm,
} = useAuthMorphCard(props);

const widthClass = computed(() => {
  if (props.maxWidth === 'lg') {
    return 'max-w-2xl';
  }

  return 'max-w-md';
});
const isAnimating = computed(() => phase.value !== 'idle');
const isFilled = computed(() => phase.value === 'filling' || phase.value === 'leaving');
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
          <h1 class="text-2xl font-semibold text-white">
            {{ title }}
          </h1>
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
