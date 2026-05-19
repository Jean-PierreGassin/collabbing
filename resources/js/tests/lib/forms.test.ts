import { describe, expect, it, vi } from 'vitest';
import { oldInputBoolean, oldInputString } from '@/lib/forms';

const session = vi.hoisted(() => ({
  oldInput: {} as Record<string, boolean | number | string | string[] | null>,
}));

vi.mock('@/stores/session', () => ({
  useSessionStore: () => session,
}));

describe('form helpers', () => {
  it('normalizes old input values into text field values', () => {
    session.oldInput = {
      active: true,
      count: 3,
      title: 'Saved title',
      tags: ['vue'],
      empty: null,
    };

    expect(oldInputString('title')).toBe('Saved title');
    expect(oldInputString('count')).toBe('3');
    expect(oldInputString('active')).toBe('true');
    expect(oldInputString('tags', 'fallback')).toBe('fallback');
    expect(oldInputString('empty', 'fallback')).toBe('fallback');
    expect(oldInputString('missing', 'fallback')).toBe('fallback');
  });

  it('normalizes browser checkbox values into booleans', () => {
    session.oldInput = {
      enabled: 'on',
      disabled: 'no',
      numeric: 1,
      explicit: false,
    };

    expect(oldInputBoolean('enabled')).toBe(true);
    expect(oldInputBoolean('disabled')).toBe(false);
    expect(oldInputBoolean('numeric')).toBe(true);
    expect(oldInputBoolean('explicit', true)).toBe(false);
    expect(oldInputBoolean('missing', true)).toBe(true);
  });
});
