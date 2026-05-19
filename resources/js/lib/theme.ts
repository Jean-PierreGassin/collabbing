import { readonly, ref } from 'vue';

export const themeModes = ['light', 'dark', 'system'] as const;

export type ThemeMode = typeof themeModes[number];
export type ResolvedTheme = Exclude<ThemeMode, 'system'>;

const storageKey = 'collabbing.themeMode';
const darkQuery = '(prefers-color-scheme: dark)';
const themeMode = ref<ThemeMode>('system');
const resolvedTheme = ref<ResolvedTheme>('dark');
let mediaQuery: MediaQueryList | null = null;
let isStarted = false;

function isThemeMode(value: string | null): value is ThemeMode {
  return value === 'light' || value === 'dark' || value === 'system';
}

function storedThemeMode(): ThemeMode {
  try {
    const storedMode = window.localStorage.getItem(storageKey);

    if (isThemeMode(storedMode)) {
      return storedMode;
    }
  } catch {
    return 'system';
  }

  return 'system';
}

function systemTheme(): ResolvedTheme {
  if (typeof window === 'undefined') {
    return 'dark';
  }

  if (!mediaQuery) {
    mediaQuery = window.matchMedia(darkQuery);
  }

  return mediaQuery.matches ? 'dark' : 'light';
}

function resolveTheme(mode: ThemeMode): ResolvedTheme {
  if (mode === 'system') {
    return systemTheme();
  }

  return mode;
}

function applyTheme(mode: ThemeMode): void {
  if (typeof document === 'undefined') {
    return;
  }

  const nextTheme = resolveTheme(mode);
  const root = document.documentElement;

  root.classList.toggle('dark', nextTheme === 'dark');
  root.classList.toggle('light', nextTheme === 'light');
  root.dataset.theme = nextTheme;
  root.dataset.themeMode = mode;
  themeMode.value = mode;
  resolvedTheme.value = nextTheme;
}

function persistThemeMode(mode: ThemeMode): void {
  try {
    if (mode === 'system') {
      window.localStorage.removeItem(storageKey);

      return;
    }

    window.localStorage.setItem(storageKey, mode);
  } catch {
    // Theme preference is an enhancement; the active page still updates.
  }
}

function syncSystemTheme(): void {
  if (themeMode.value === 'system') {
    applyTheme('system');
  }
}

export function setThemeMode(mode: ThemeMode): void {
  persistThemeMode(mode);
  applyTheme(mode);
}

export function startThemeMode(): void {
  if (typeof window === 'undefined' || isStarted) {
    return;
  }

  isStarted = true;
  mediaQuery = window.matchMedia(darkQuery);
  applyTheme(storedThemeMode());

  if (mediaQuery.addEventListener) {
    mediaQuery.addEventListener('change', syncSystemTheme);

    return;
  }

  mediaQuery.addListener(syncSystemTheme);
}

export function useThemeMode() {
  return {
    resolvedTheme: readonly(resolvedTheme),
    setThemeMode,
    themeMode: readonly(themeMode),
    themeModes,
  };
}
