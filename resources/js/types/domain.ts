export interface DomainUser {
  id: number;
  username: string;
  firstName: string;
  lastName: string;
  name: string;
  email: string | null;
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
  contentHtml: string;
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
  parentId: number | null;
  content: string;
  contentHtml: string;
  createdAtForHumans: string;
  updatedAtForHumans: string;
  wasEdited: boolean;
  user: DomainUser;
  replies: IdeaComment[];
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

export interface RepositoryEvent {
  id: number;
  type: string;
  summary: string;
  occurredAtForHumans: string;
}

export interface IdeaRepositoryActivity {
  htmlUrl: string | null;
  defaultBranch: string | null;
  isMissing: boolean;
  openIssuesCount: number;
  stargazersCount: number;
  forksCount: number;
  lastPushedAtForHumans: string | null;
  lastSyncedAtForHumans: string | null;
  latestCommitSha: string | null;
  latestCommitShortSha: string | null;
  latestCommitMessage: string | null;
  latestCommitAuthor: string | null;
  events: RepositoryEvent[];
}

export interface Idea {
  id: number;
  title: string;
  titleDisplay: string;
  summary: string;
  communication: string | null;
  content: string;
  contentHtml: string;
  status: string;
  statusDisplay: string;
  repository: boolean;
  repositoryName: string | null;
  repositoryActivity: IdeaRepositoryActivity;
  createdAtForHumans: string;
  user: DomainUser;
  supportersCount: number;
  approvedApplicationsCount: number;
  collaborators: IdeaApplication[];
  hiddenCollaboratorsCount: number;
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
    repositoryActivity: string;
  };
}

export interface Paginator<T> {
  items: T[];
  currentPage: number;
  lastPage: number;
  previousPageUrl: string | null;
  nextPageUrl: string | null;
}
