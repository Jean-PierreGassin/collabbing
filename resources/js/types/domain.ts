export interface DomainUser {
  id: number;
  username: string;
  firstName: string;
  lastName: string;
  name: string;
  email: string;
  bio: string | null;
  bioHtml: string | null;
  githubUsername: string | null;
  hasGithubToken: boolean;
  profilePicture: string;
  createdAtFormatted: string;
  canUpdate: boolean;
  routes: {
    show: string;
    edit: string;
    update: string;
    githubLogin: string;
    githubRevoke: string;
  };
}

export interface IdeaApplication {
  id: number;
  content: string;
  status: string;
  createdAtForHumans: string;
  user: DomainUser;
  routes: {
    destroy: string;
    approve: string;
  };
}

export interface IdeaComment {
  id: number;
  content: string;
  createdAtForHumans: string;
  updatedAtForHumans: string;
  wasEdited: boolean;
  user: DomainUser;
  can: {
    update: boolean;
  };
  routes: {
    edit: string;
    update: string;
  };
}

export interface IdeaSupporter {
  id: number;
  routes: {
    destroy: string;
  };
}

export interface Idea {
  id: number;
  title: string;
  titleDisplay: string;
  communication: string | null;
  content: string;
  contentHtml: string;
  status: string;
  statusDisplay: string;
  repository: string | null;
  repositoryName: string | null;
  createdAtForHumans: string;
  user: DomainUser;
  supportersCount: number;
  approvedApplicationsCount: number;
  collaborators: IdeaApplication[];
  can: {
    update: boolean;
    storeApplication: boolean;
    storeSupporter: boolean;
    deleteApplication: boolean;
    updateApplication: boolean;
  };
  routes: {
    show: string;
    edit: string;
    dashboard: string;
    update: string;
    applicationsCreate: string;
    applicationsStore: string;
    commentsStore: string;
    supportersStore: string;
    repositoryCreate: string;
    repositoryInvite: string;
  };
}

export interface Paginator<T> {
  items: T[];
  currentPage: number;
  lastPage: number;
  previousPageUrl: string | null;
  nextPageUrl: string | null;
}
