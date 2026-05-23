import { computed } from 'vue';
import { defineStore } from 'pinia';
import { useSharedPage } from '@/lib/page';

export const useSessionStore = defineStore('session', () => {
  const page = useSharedPage();

  const user = computed(() => page.props.auth.user);
  const routes = computed(() => page.props.routes);
  const flash = computed(() => page.props.flash);
  const errors = computed(() => page.props.errors);
  const oldInput = computed(() => page.props.oldInput);
  const csrfToken = computed(() => document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? null);
  const isAuthenticated = computed(() => user.value !== null);

  return {
    csrfToken,
    errors,
    flash,
    isAuthenticated,
    oldInput,
    routes,
    user,
  };
});
