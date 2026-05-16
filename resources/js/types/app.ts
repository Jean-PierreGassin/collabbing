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
  feedback: string;
  pricing: string;
}

export interface AppFlash {
  status: string | null;
  errors: string[];
}

export interface SharedPageProps {
  [key: string]: unknown;
  auth: {
    user: DomainUser | null;
  };
  errors: Record<string, string>;
  flash: AppFlash;
  routes: AppRoutes;
}
