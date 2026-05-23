import { computed } from 'vue';
import { useSharedPage } from '@/lib/page';
import type { DomainUser, Idea } from '@/types/domain';

const defaultDescription = 'Share early product ideas, find collaborators, and move promising projects toward real work.';

function excerpt(value: string | null | undefined, fallback: string): string {
  if (!value) {
    return fallback;
  }

  return value
    .replace(/[#*_`>\-[\]()]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
    .slice(0, 155) || fallback;
}

export function useAppShellPageState() {
  const page = useSharedPage();

  const seo = computed(() => {
    const idea = page.props.idea as Idea | undefined;
    const user = page.props.user as DomainUser | undefined;

    if (idea && page.component === 'Ideas/Show') {
      return {
        title: idea.titleDisplay,
        pageTitle: `${idea.titleDisplay} | Collabbing`,
        description: excerpt(idea.summary, defaultDescription),
        type: 'article',
      };
    }

    if (user && page.component === 'Users/Show') {
      return {
        title: `${user.name} (@${user.username})`,
        pageTitle: `${user.name} (@${user.username}) | Collabbing`,
        description: excerpt(user.bio, `${user.name} is a member of the Collabbing builder community.`),
        type: 'profile',
      };
    }

    let ideaFormTitle = 'Share an Idea';
    let ideaManageTitle = 'Manage Idea';
    let userFormTitle = 'Create Profile';

    if (idea) {
      ideaFormTitle = 'Edit Idea';
      ideaManageTitle = `Manage ${idea.titleDisplay}`;
    }

    if (user) {
      userFormTitle = 'Edit Profile';
    }

    const titles: Record<string, string> = {
      'Auth/Login': 'Login',
      'Auth/PasswordEmail': 'Reset Password',
      'Auth/PasswordReset': 'Choose a New Password',
      'Auth/Register': 'Register',
      'Comments/Form': 'Comment',
      Contact: 'Contact',
      Dashboard: 'Dashboard',
      Error: 'Page Error',
      Feedback: 'Feedback',
      Home: 'Collabbing',
      'Ideas/Apply': 'Apply to Collaborate',
      'Ideas/Form': ideaFormTitle,
      'Ideas/Index': 'Ideas',
      'Ideas/Manage': ideaManageTitle,
      Pricing: 'Pricing',
      Resources: 'Resources',
      'Users/Form': userFormTitle,
      'Users/Index': 'Members',
    };

    const title = titles[page.component] ?? 'Collabbing';
    let pageTitle = `${title} | Collabbing`;

    if (title === 'Collabbing') {
      pageTitle = title;
    }

    return {
      title,
      pageTitle,
      description: defaultDescription,
      type: 'website',
    };
  });

  const canonicalUrl = computed(() => {
    if (typeof window === 'undefined') {
      return undefined;
    }

    return `${window.location.origin}${page.url.split('#')[0]}`;
  });

  const transitionKey = computed(() => {
    const [
      pathAndQuery,
      hash = '',
    ] = page.url.split('#');
    const [
      path,
      query = '',
    ] = pathAndQuery.split('?');
    const params = new URLSearchParams(query);

    params.delete('comments');

    const nextQuery = params.toString();
    let nextUrl = path;

    if (nextQuery) {
      nextUrl = `${nextUrl}?${nextQuery}`;
    }

    if (hash) {
      nextUrl = `${nextUrl}#${hash}`;
    }

    return nextUrl;
  });

  const currentSearchTerm = computed(() => {
    const [
      ,
      query = '',
    ] = page.url.split('?');
    const params = new URLSearchParams(query.split('#')[0]);
    const search = params.get('search');

    return search ?? '';
  });

  return {
    canonicalUrl,
    currentSearchTerm,
    seo,
    transitionKey,
  };
}
