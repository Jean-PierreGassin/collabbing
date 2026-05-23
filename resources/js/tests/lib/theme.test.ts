import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { ThemeMode } from '@/lib/theme';

type ThemeListener = () => void;

let matchesDark = true;
let listeners: ThemeListener[] = [];

function stubMatchMedia(): void {
  vi.stubGlobal('matchMedia', vi.fn(() => ({
    addEventListener: (_event: string, listener: ThemeListener) => {
      listeners.push(listener);
    },
    addListener: (listener: ThemeListener) => {
      listeners.push(listener);
    },
    get matches() {
      return matchesDark;
    },
    media: '(prefers-color-scheme: dark)',
    removeEventListener: (_event: string, listener: ThemeListener) => {
      listeners = listeners.filter((candidate) => candidate !== listener);
    },
    removeListener: (listener: ThemeListener) => {
      listeners = listeners.filter((candidate) => candidate !== listener);
    },
  })));
}

async function themeModule() {
  vi.resetModules();

  return import('@/lib/theme');
}

function setStoredThemeMode(mode: ThemeMode): void {
  window.localStorage.setItem('collabbing.themeMode', mode);
}

describe('theme mode', () => {
  beforeEach(() => {
    matchesDark = true;
    listeners = [];
    window.localStorage.clear();
    document.documentElement.className = '';
    document.documentElement.removeAttribute('data-theme');
    document.documentElement.removeAttribute('data-theme-mode');
    stubMatchMedia();
  });

  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('starts from a stored light mode preference', async () => {
    setStoredThemeMode('light');

    const { startThemeMode, useThemeMode } = await themeModule();

    startThemeMode();

    expect(document.documentElement.classList.contains('light')).toBe(true);
    expect(document.documentElement.classList.contains('dark')).toBe(false);
    expect(document.documentElement.dataset.theme).toBe('light');
    expect(useThemeMode().themeMode.value).toBe('light');
    expect(useThemeMode().resolvedTheme.value).toBe('light');
  });

  it('stores explicit theme choices', async () => {
    const { setThemeMode, startThemeMode, useThemeMode } = await themeModule();

    startThemeMode();
    setThemeMode('dark');

    expect(window.localStorage.getItem('collabbing.themeMode')).toBe('dark');
    expect(document.documentElement.classList.contains('dark')).toBe(true);
    expect(useThemeMode().resolvedTheme.value).toBe('dark');
  });

  it('uses system mode and reacts to system preference changes', async () => {
    const { setThemeMode, startThemeMode, useThemeMode } = await themeModule();

    matchesDark = false;
    startThemeMode();

    expect(document.documentElement.dataset.theme).toBe('light');
    expect(useThemeMode().themeMode.value).toBe('system');

    setThemeMode('system');
    matchesDark = true;
    listeners.forEach((listener) => listener());

    expect(window.localStorage.getItem('collabbing.themeMode')).toBeNull();
    expect(document.documentElement.classList.contains('dark')).toBe(true);
    expect(useThemeMode().resolvedTheme.value).toBe('dark');
  });
});
