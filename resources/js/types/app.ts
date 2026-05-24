import type { DomainUser } from '@/types/domain';

export interface AppRoutes {
  home: string;
  dashboard: string;
  login: string;
  logout: string;
  register: string;
  passwordRequest: string;
  passwordEmail: string;
  passwordReset: string;
  ideas: string;
  ideasCreate: string;
  ideasStore: string;
  users: string;
  feedback: string;
  contact: string;
  pricing: string;
}

export interface AppFlash {
  status: string | null;
  errors: string[];
  repositoryInvitePrompt: boolean;
  repositoryAccessPrompt: boolean;
}

export type OldInputValue = boolean | number | string | string[] | null;

export interface SharedPageProps {
  [key: string]: unknown;
  auth: {
    user: DomainUser | null;
  };
  errors: Record<string, string>;
  flash: AppFlash;
  oldInput: Record<string, OldInputValue>;
  routes: AppRoutes;
}
