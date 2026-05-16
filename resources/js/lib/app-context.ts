import type { AppContext } from '@/types/app';

const fallbackRoutes = {
  home: '/',
  app: '/app',
  appIdeas: '/app/ideas',
  appDashboard: '/app/dashboard',
  appResources: '/app/resources',
  classicIdeas: '/ideas',
  classicDashboard: '/dashboard',
  login: '/users/login',
  register: '/users/register',
  feedback: '/resources/feedback',
  pricing: '/resources/pricing',
};

export function getAppContext(): AppContext {
  const pageContext = window.__COLLABBING__ ?? {};
  const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? null;

  return {
    appName: pageContext.appName ?? 'Collabbing',
    csrfToken,
    user: pageContext.user ?? null,
    routes: {
      ...fallbackRoutes,
      ...pageContext.routes,
    },
  };
}
