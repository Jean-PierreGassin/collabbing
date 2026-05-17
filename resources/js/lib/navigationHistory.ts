import { router } from '@inertiajs/vue3';
import { readonly, ref } from 'vue';
import type { AppRoutes } from '@/types/app';

const storageCurrentKey = 'collabbing.navigation.currentUrl';
const storageReturnKey = 'collabbing.navigation.returnUrl';

const currentUrl = ref<string | null>(null);
const returnUrl = ref<string | null>(null);
let isStarted = false;

function normalizeUrl(url: string): string {
  const parsed = new URL(url, window.location.origin);

  parsed.searchParams.delete('comments');

  return `${parsed.pathname}${parsed.search}${parsed.hash}`;
}

function storedUrl(key: string): string | null {
  try {
    return window.sessionStorage.getItem(key);
  } catch {
    return null;
  }
}

function storeUrl(key: string, value: string | null): void {
  try {
    if (value) {
      window.sessionStorage.setItem(key, value);

      return;
    }

    window.sessionStorage.removeItem(key);
  } catch {
    // Session storage is an enhancement; navigation still falls back to parent links.
  }
}

function sameOriginReferrer(): string | null {
  if (!document.referrer) {
    return null;
  }

  const referrer = new URL(document.referrer);

  if (referrer.origin !== window.location.origin) {
    return null;
  }

  return normalizeUrl(document.referrer);
}

function navigationScope(url: string | null): string | null {
  if (!url) {
    return null;
  }

  const path = new URL(url, window.location.origin).pathname;
  const ideaMatch = path.match(/^\/ideas\/([^/]+)/);

  if (ideaMatch) {
    return `idea:${ideaMatch[1]}`;
  }

  return path;
}

function isSameNavigationScope(firstUrl: string | null, secondUrl: string | null): boolean {
  const firstScope = navigationScope(firstUrl);
  const secondScope = navigationScope(secondUrl);

  return Boolean(firstScope && secondScope && firstScope === secondScope);
}

function setCurrentUrl(nextUrl: string): void {
  const normalizedNextUrl = normalizeUrl(nextUrl);

  if (normalizedNextUrl === currentUrl.value) {
    return;
  }

  if (! isSameNavigationScope(currentUrl.value, normalizedNextUrl)) {
    returnUrl.value = currentUrl.value;
  }

  currentUrl.value = normalizedNextUrl;

  storeUrl(storageReturnKey, returnUrl.value);
  storeUrl(storageCurrentKey, currentUrl.value);
}

export function startNavigationHistory(initialUrl: string): void {
  if (typeof window === 'undefined') {
    return;
  }

  const normalizedInitialUrl = normalizeUrl(initialUrl);
  const storedCurrentUrl = storedUrl(storageCurrentKey);
  const storedReturnUrl = storedUrl(storageReturnKey);
  const referrerUrl = sameOriginReferrer();

  currentUrl.value = normalizedInitialUrl;
  returnUrl.value = storedCurrentUrl && storedCurrentUrl !== normalizedInitialUrl && ! isSameNavigationScope(storedCurrentUrl, normalizedInitialUrl)
    ? storedCurrentUrl
    : storedReturnUrl && storedReturnUrl !== normalizedInitialUrl
      ? storedReturnUrl
      : referrerUrl && referrerUrl !== normalizedInitialUrl && ! isSameNavigationScope(referrerUrl, normalizedInitialUrl)
        ? referrerUrl
        : null;

  storeUrl(storageCurrentKey, currentUrl.value);
  storeUrl(storageReturnKey, returnUrl.value);

  if (isStarted) {
    return;
  }

  isStarted = true;

  router.on('navigate', (event) => {
    setCurrentUrl(event.detail.page.url);
  });
}

export function useNavigationHistory() {
  return {
    currentUrl: readonly(currentUrl),
    returnUrl: readonly(returnUrl),
  };
}

export function labelForUrl(url: string | null, routes: AppRoutes): string | null {
  if (!url || typeof window === 'undefined') {
    return null;
  }

  const path = new URL(url, window.location.origin).pathname;

  if (path === new URL(routes.dashboard, window.location.origin).pathname) {
    return 'Dashboard';
  }

  if (path === new URL(routes.ideas, window.location.origin).pathname) {
    return 'Ideas';
  }

  if (path === new URL(routes.users, window.location.origin).pathname) {
    return 'Members';
  }

  if (/^\/ideas\/[^/]+\/dashboard$/.test(path)) {
    return 'Manage';
  }

  if (/^\/ideas\/[^/]+\/edit$/.test(path)) {
    return 'Edit idea';
  }

  if (/^\/ideas\/[^/]+\/applications\/create$/.test(path)) {
    return 'Application';
  }

  if (/^\/ideas\/[^/]+$/.test(path)) {
    return 'Idea overview';
  }

  return 'Previous page';
}
