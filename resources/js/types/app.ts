export interface AppUser {
  id: number;
  name: string;
  username: string;
  email: string;
}

export interface AppRoutes {
  home: string;
  app: string;
  appIdeas: string;
  appDashboard: string;
  appResources: string;
  classicIdeas: string;
  classicDashboard: string;
  login: string;
  register: string;
  feedback: string;
  pricing: string;
}

export interface AppContext {
  appName: string;
  csrfToken: string | null;
  user: AppUser | null;
  routes: AppRoutes;
}

declare global {
  interface Window {
    __COLLABBING__?: Partial<AppContext>;
  }
}
