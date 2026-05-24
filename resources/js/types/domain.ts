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
  contributionType: string | null;
  contributionTypeDisplay: string;
  firstAction: string | null;
  status: string;
  statusDisplay: string;
  createdAtForHumans: string;
  user: DomainUser;
  thread: IdeaApplicationThread | null;
  routes: {
    destroy: string;
    edit: string;
    update: string;
    approve: string;
  };
}

export interface IdeaApplicationMessage {
  id: number;
  type: string;
  body: string | null;
  bodyHtml: string | null;
  isSystem: boolean;
  occurredAtForHumans: string | null;
  user: DomainUser | null;
}

export interface IdeaApplicationThread {
  messages: IdeaApplicationMessage[];
  unreadCount: number;
  hasUnread: boolean;
  canMessage: boolean;
  isReadOnly: boolean;
  readOnlyReason: string | null;
  routes: {
    read: string;
    store: string;
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

export interface IdeaTag {
  name: string;
  count: number;
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

export interface IdeaCollaborationReadinessBadges {
  applicationsOpen: boolean;
  firstStepListed: boolean;
  repoAvailable: boolean;
  startNotesReady: boolean;
}

export interface IdeaCollaboration {
  stage: string | null;
  stageDisplay: string;
  helpWanted: string[];
  helpWantedDisplay: string[];
  helpWantedNote: string | null;
  firstContribution: string | null;
  applicationsOpen: boolean;
  applicationsClosedNote: string | null;
  communicationStyle: string | null;
  communicationStyleDisplay: string;
  communicationNote: string | null;
  gettingStartedNotesReady: boolean;
  gettingStartedNotes: string | null;
  gettingStartedNotesHtml: string | null;
  gettingStartedNotesUpdatedAtForHumans: string | null;
  readinessBadges: IdeaCollaborationReadinessBadges;
}

export interface Idea {
  id: number;
  title: string;
  titleDisplay: string;
  tagline: string;
  summary: string;
  tags: string[];
  communication: string | null;
  collaboration: IdeaCollaboration;
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
  pendingApplicationsCount: number | null;
  collaborators: IdeaApplication[];
  hiddenCollaboratorsCount: number;
  can: {
    update: boolean;
    storeApplication: boolean;
    storeSupporter: boolean;
    deleteApplication: boolean;
    updateApplication: boolean;
    storeComment: boolean;
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
