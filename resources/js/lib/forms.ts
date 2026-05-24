import { useSessionStore } from '@/stores/session';
import type { OldInputValue } from '@/types/app';

export function fieldErrors(name: string): string[] {
  const session = useSessionStore();
  const error = session.errors[name];

  if (!error) {
    return [];
  }

  return [error];
}

function oldInput(name: string): OldInputValue | undefined {
  const session = useSessionStore();

  return session.oldInput[name];
}

export function hasOldInput(names: string[]): boolean {
  const session = useSessionStore();

  return names.some((name) => Object.prototype.hasOwnProperty.call(session.oldInput, name));
}

export function oldInputString(name: string, fallback: string | null | undefined = ''): string {
  const value = oldInput(name);

  if (typeof value === 'string') {
    return value;
  }

  if (typeof value === 'number' || typeof value === 'boolean') {
    return String(value);
  }

  return fallback ?? '';
}

export function oldInputStringArray(name: string, fallback: string[] = []): string[] {
  const value = oldInput(name);

  if (Array.isArray(value)) {
    return value.filter((item): item is string => typeof item === 'string');
  }

  if (typeof value === 'string' && value !== '') {
    return [value];
  }

  return fallback;
}

export function oldInputBoolean(name: string, fallback = false): boolean {
  const value = oldInput(name);

  if (typeof value === 'boolean') {
    return value;
  }

  if (typeof value === 'number') {
    return value === 1;
  }

  if (typeof value === 'string') {
    return [
      '1',
      'on',
      'true',
      'yes',
    ].includes(value.toLowerCase());
  }

  return fallback;
}
