import { usePage } from '@inertiajs/vue3';
import type { SharedPageProps } from '@/types/app';

export function useSharedPage() {
  return usePage<SharedPageProps>();
}
