<script setup lang="ts">
import { computed, ref } from 'vue';
import { Check, ChevronDown, GitBranch, LinkIcon, UserRound } from '@lucide/vue';
import PaginationLinks from '@/components/pagination/PaginationLinks.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import UserAvatar from '@/components/users/UserAvatar.vue';
import type { DomainUser } from '@/types/domain';
import type { Paginator } from '@/types/domain';

const props = defineProps<{
  users: Paginator<DomainUser>;
}>();

const bioPreviewLength = 140;
const expandedBioIds = ref<Set<number>>(new Set());
const copiedUserId = ref<number | null>(null);
let copiedResetTimer: number | null = null;

const hasMembers = computed(() => props.users.items.length > 0);

function isBioExpanded(user: DomainUser): boolean {
  return expandedBioIds.value.has(user.id);
}

function hasExpandableBio(user: DomainUser): boolean {
  return (user.bio?.length ?? 0) > bioPreviewLength;
}

function toggleBio(user: DomainUser): void {
  const nextExpandedBioIds = new Set(expandedBioIds.value);

  if (nextExpandedBioIds.has(user.id)) {
    nextExpandedBioIds.delete(user.id);
  } else {
    nextExpandedBioIds.add(user.id);
  }

  expandedBioIds.value = nextExpandedBioIds;
}

function githubUrl(user: DomainUser): string | null {
  if (! user.githubUsername) {
    return null;
  }

  return `https://github.com/${encodeURIComponent(user.githubUsername)}`;
}

function githubAvailabilityLabel(user: DomainUser): string {
  if (user.githubUsername && user.hasGithubToken) {
    return 'GitHub connected';
  }

  if (user.githubUsername) {
    return 'GitHub profile available';
  }

  return 'GitHub not listed';
}

function githubAvailabilityVariant(user: DomainUser): 'default' | 'secondary' | 'outline' {
  if (user.githubUsername && user.hasGithubToken) {
    return 'default';
  }

  if (user.githubUsername) {
    return 'secondary';
  }

  return 'outline';
}

function profileUrl(user: DomainUser): string {
  if (typeof window === 'undefined') {
    return user.routes.show;
  }

  return new URL(user.routes.show, window.location.origin).toString();
}

function setCopiedUser(user: DomainUser): void {
  copiedUserId.value = user.id;

  if (copiedResetTimer !== null) {
    window.clearTimeout(copiedResetTimer);
  }

  copiedResetTimer = window.setTimeout(() => {
    copiedUserId.value = null;
    copiedResetTimer = null;
  }, 2200);
}

function fallbackCopy(text: string): void {
  const textArea = document.createElement('textarea');

  textArea.value = text;
  textArea.setAttribute('readonly', 'true');
  textArea.style.position = 'fixed';
  textArea.style.opacity = '0';
  document.body.appendChild(textArea);
  textArea.select();
  document.execCommand('copy');
  document.body.removeChild(textArea);
}

async function copyProfileLink(user: DomainUser): Promise<void> {
  const link = profileUrl(user);

  if (navigator.clipboard?.writeText) {
    await navigator.clipboard.writeText(link);
  } else {
    fallbackCopy(link);
  }

  setCopiedUser(user);
}
</script>

<template>
  <section class="flex flex-col gap-5">
    <div class="flex flex-col gap-1">
      <h1 class="text-2xl font-semibold text-white">Members</h1>
      <p class="max-w-2xl text-sm leading-6 text-muted-foreground">
        Find people building, supporting, and collaborating on ideas.
      </p>
    </div>

    <div
      v-if="hasMembers"
      class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
      <article
        v-for="user in props.users.items"
        :key="user.id"
        class="group flex min-w-0 flex-col gap-4 rounded-md border border-border bg-card/90 p-4 transition-[border-color,background-color,box-shadow,transform] duration-150 ease-out hover:-translate-y-px hover:border-primary/35 hover:bg-card focus-within:border-primary/55 focus-within:ring-2 focus-within:ring-ring/35"
      >
        <div class="flex min-w-0 gap-3">
          <UserAvatar
            :src="user.profilePicture"
            :alt="`${user.name} profile picture`" />
          <div class="flex min-w-0 flex-1 flex-col gap-1">
            <a
              class="truncate font-semibold text-white transition-colors hover:text-primary"
              :href="user.routes.show">
              {{ user.name }}
            </a>
            <span class="truncate text-sm text-muted-foreground">@{{ user.username }}</span>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <Badge
            :variant="githubAvailabilityVariant(user)"
            class="gap-1.5">
            <GitBranch
              class="size-3.5"
              aria-hidden="true" />
            {{ githubAvailabilityLabel(user) }}
          </Badge>
          <a
            v-if="githubUrl(user)"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-primary transition-colors hover:text-white hover:underline"
            :href="githubUrl(user) ?? undefined"
            target="_blank"
            rel="noopener noreferrer"
          >
            @{{ user.githubUsername }}
          </a>
        </div>

        <div class="flex flex-1 flex-col gap-2">
          <p
            v-if="user.bio"
            :class="[
              'break-words text-sm leading-6 text-muted-foreground [overflow-wrap:anywhere]',
              ! isBioExpanded(user) && hasExpandableBio(user) ? 'line-clamp-3' : undefined,
            ]"
          >
            {{ user.bio }}
          </p>
          <p
            v-else
            class="text-sm leading-6 text-muted-foreground">
            This member has not added a bio yet.
          </p>

          <button
            v-if="user.bio && hasExpandableBio(user)"
            type="button"
            class="inline-flex w-fit items-center gap-1 text-sm font-medium text-primary transition-colors hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
            :aria-expanded="isBioExpanded(user)"
            @click="toggleBio(user)"
          >
            {{ isBioExpanded(user) ? 'Hide bio' : 'Show bio' }}
            <ChevronDown
              :class="['size-4 transition-transform duration-200', isBioExpanded(user) ? 'rotate-180' : undefined]"
              aria-hidden="true"
            />
          </button>
        </div>

        <div class="mt-auto flex flex-wrap items-center gap-2 border-t border-border pt-4">
          <Button
            :as="'a'"
            :href="user.routes.show"
            size="sm"
            class="flex-1 sm:flex-none">
            <UserRound
              class="size-4"
              aria-hidden="true" />
            View profile
          </Button>
          <Button
            :aria-label="`Copy ${user.name} profile link`"
            :title="`Copy ${user.name} profile link`"
            size="sm"
            variant="outline"
            class="flex-1 sm:flex-none"
            @click="copyProfileLink(user)"
          >
            <Check
              v-if="copiedUserId === user.id"
              class="size-4"
              aria-hidden="true" />
            <LinkIcon
              v-else
              class="size-4"
              aria-hidden="true" />
            {{ copiedUserId === user.id ? 'Copied' : 'Copy link' }}
          </Button>
        </div>
      </article>
    </div>

    <div
      v-else
      class="rounded-md border border-dashed border-border p-6 text-sm text-muted-foreground">
      No members are available yet.
    </div>

    <p
      class="sr-only"
      aria-live="polite">
      <template v-if="copiedUserId !== null">Profile link copied.</template>
    </p>

    <PaginationLinks :paginator="props.users" />
  </section>
</template>
