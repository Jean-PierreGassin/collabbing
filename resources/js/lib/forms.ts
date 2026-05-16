import { useSessionStore } from '@/stores/session';

export function fieldErrors(name: string): string[] {
  const session = useSessionStore();
  const error = session.errors[name];

  return error ? [error] : [];
}
