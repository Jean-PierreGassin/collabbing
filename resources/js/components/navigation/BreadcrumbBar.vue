<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { labelForUrl, useNavigationHistory } from '@/lib/navigationHistory';
import { useSharedPage } from '@/lib/page';
import { useSessionStore } from '@/stores/session';
import type { DomainUser, Idea, IdeaComment } from '@/types/domain';

interface BreadcrumbCrumb {
  href?: string;
  label: string;
}

const page = useSharedPage();
const session = useSessionStore();
const { returnUrl } = useNavigationHistory();

function parentCrumb(fallbackLabel: string, fallbackHref: string): BreadcrumbCrumb {
  const contextualLabel = labelForUrl(returnUrl.value, session.routes);

  if (returnUrl.value && (contextualLabel === 'Dashboard' || contextualLabel === 'Ideas' || contextualLabel === 'Members')) {
    return {
      href: returnUrl.value,
      label: contextualLabel,
    };
  }

  return {
    href: fallbackHref,
    label: fallbackLabel,
  };
}

function ideaCrumbs(idea: Idea, currentLabel?: string): BreadcrumbCrumb[] {
  let href: string | undefined;

  if (currentLabel) {
    href = idea.routes.show;
  }

  const crumbs: BreadcrumbCrumb[] = [
    parentCrumb('Ideas', session.routes.ideas),
    {
      href,
      label: `Idea - ${idea.titleDisplay}`,
    },
  ];

  if (currentLabel) {
    crumbs.push({ label: currentLabel });
  }

  return crumbs;
}

function userCrumbs(user: DomainUser, currentLabel?: string): BreadcrumbCrumb[] {
  let href: string | undefined;

  if (currentLabel) {
    href = user.routes.show;
  }

  const crumbs: BreadcrumbCrumb[] = [
    parentCrumb('Members', session.routes.users),
    {
      href,
      label: `Member - ${user.name}`,
    },
  ];

  if (currentLabel) {
    crumbs.push({ label: currentLabel });
  }

  return crumbs;
}

const breadcrumbs = computed<BreadcrumbCrumb[]>(() => {
  const idea = page.props.idea as Idea | undefined;
  const user = page.props.user as DomainUser | undefined;
  const comment = page.props.comment as IdeaComment | undefined;

  if (page.component === 'Home') {
    return [{ label: 'Home' }];
  }

  if (page.component === 'Dashboard') {
    return [{ label: 'Dashboard' }];
  }

  if (page.component === 'Ideas/Index') {
    return [{ label: 'Ideas' }];
  }

  if (idea && page.component === 'Ideas/Show') {
    return ideaCrumbs(idea);
  }

  if (idea && page.component === 'Ideas/Manage') {
    return ideaCrumbs(idea, 'Manage');
  }

  if (idea && page.component === 'Ideas/Form') {
    return ideaCrumbs(idea, 'Edit');
  }

  if (!idea && page.component === 'Ideas/Form') {
    return [
      { href: session.routes.ideas, label: 'Ideas' },
      { label: 'Create' },
    ];
  }

  if (idea && page.component === 'Ideas/Apply') {
    return ideaCrumbs(idea, 'Apply');
  }

  if (idea && page.component === 'Comments/Form') {
    let commentLabel = 'Comment';

    if (comment) {
      commentLabel = 'Edit comment';
    }

    return ideaCrumbs(idea, commentLabel);
  }

  if (page.component === 'Users/Index') {
    return [{ label: 'Members' }];
  }

  if (user && page.component === 'Users/Show') {
    return userCrumbs(user);
  }

  if (user && page.component === 'Users/Form') {
    return userCrumbs(user, 'Edit');
  }

  if (!user && page.component === 'Users/Form') {
    return [
      { href: session.routes.users, label: 'Members' },
      { label: 'Create' },
    ];
  }

  if (page.component === 'Resources') {
    return [{ label: 'Resources' }];
  }

  if (page.component === 'Pricing') {
    return [
      { label: 'Resources' },
      { label: 'Pricing' },
    ];
  }

  if (page.component === 'Feedback') {
    return [
      { label: 'Resources' },
      { label: 'Feedback' },
    ];
  }

  if (page.component === 'Contact') {
    return [
      { label: 'Resources' },
      { label: 'Contact' },
    ];
  }

  if (page.component === 'Auth/Login') {
    return [{ label: 'Login' }];
  }

  if (page.component === 'Auth/Register') {
    return [{ label: 'Register' }];
  }

  if (page.component === 'Auth/PasswordEmail' || page.component === 'Auth/PasswordReset') {
    return [{ label: 'Reset password' }];
  }

  if (page.component === 'Error') {
    return [{ label: 'Page error' }];
  }

  return [{ label: 'Collabbing' }];
});
const breadcrumbTransitionKey = computed(() => breadcrumbs.value
  .map((crumb) => `${crumb.href ?? ''}:${crumb.label}`)
  .join('|'));

function ariaCurrent(index: number): 'page' | undefined {
  if (index === breadcrumbs.value.length - 1) {
    return 'page';
  }

  return undefined;
}
</script>

<template>
  <div class="border-b border-primary/15 bg-primary/[0.07]">
    <nav class="mx-auto flex min-h-9 w-full max-w-7xl items-center overflow-x-auto px-4 py-2 text-xs font-medium sm:px-6 lg:px-8" aria-label="Breadcrumb">
      <div class="breadcrumb-transition-frame">
        <Transition name="breadcrumb-fade" appear>
          <ol :key="breadcrumbTransitionKey" class="breadcrumb-transition-panel flex min-w-0 items-center gap-2 whitespace-nowrap">
            <li v-for="(crumb, index) in breadcrumbs" :key="`${crumb.label}-${index}`" class="flex min-w-0 items-center gap-2">
              <span v-if="index > 0" class="text-primary/70" aria-hidden="true">/</span>
              <Link
                v-if="crumb.href && index < breadcrumbs.length - 1"
                :href="crumb.href"
                class="max-w-56 truncate rounded-sm text-primary underline-offset-4 transition-colors hover:text-white hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
              >
                {{ crumb.label }}
              </Link>
              <span v-else class="max-w-64 truncate text-white" :aria-current="ariaCurrent(index)">
                {{ crumb.label }}
              </span>
            </li>
          </ol>
        </Transition>
      </div>
    </nav>
  </div>
</template>
