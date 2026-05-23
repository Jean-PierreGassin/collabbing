<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{
  alt: string;
  class?: string;
  loading?: 'eager' | 'lazy';
  size?: 'sm' | 'md' | 'lg';
  src: string;
}>(), {
  class: undefined,
  loading: 'lazy',
  size: 'md',
});

const isLoaded = ref(false);
const image = ref<HTMLImageElement | null>(null);

const sizeClass = computed(() => ({
  sm: 'size-10',
  md: 'size-12',
  lg: 'size-16',
}[props.size]));

watch(
  () => props.src,
  () => {
    isLoaded.value = false;
  },
);

onMounted(() => {
  if (image.value?.complete) {
    isLoaded.value = true;
  }
});

const placeholderOpacityClass = computed(() => {
  if (isLoaded.value) {
    return 'opacity-0';
  }

  return 'opacity-100';
});

const imageOpacityClass = computed(() => {
  if (isLoaded.value) {
    return 'opacity-100';
  }

  return 'opacity-0';
});
</script>

<template>
  <span
    :class="cn(
      'relative inline-flex shrink-0 overflow-hidden rounded-full border border-border bg-secondary/40',
      sizeClass,
      props.class,
    )"
  >
    <span
      aria-hidden="true"
      :class="cn(
        'absolute inset-0 bg-gradient-to-br from-secondary via-card to-background transition-opacity duration-200 ease-out',
        placeholderOpacityClass,
      )"
    />
    <img
      ref="image"
      class="relative h-full w-full object-cover transition-opacity duration-200 ease-out motion-reduce:transition-none"
      :class="imageOpacityClass"
      :src="src"
      :alt="alt"
      :loading="loading"
      decoding="async"
      @load="isLoaded = true"
    >
  </span>
</template>
