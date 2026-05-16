import { computed, ref } from 'vue';
import { defineStore } from 'pinia';
import { getAppContext } from '@/lib/app-context';

export const useSessionStore = defineStore('session', () => {
  const context = getAppContext();
  const user = ref(context.user);
  const appName = ref(context.appName);
  const routes = ref(context.routes);

  const isAuthenticated = computed(() => user.value !== null);

  return {
    appName,
    isAuthenticated,
    routes,
    user,
  };
});
