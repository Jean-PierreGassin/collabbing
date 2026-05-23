<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import UserAvatar from '@/components/users/UserAvatar.vue';
import type { DomainUser } from '@/types/domain';

defineProps<{
  user: DomainUser;
}>();
</script>

<template>
  <Card>
    <CardHeader class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
      <div class="flex min-w-0 gap-4">
        <UserAvatar
          :src="user.profilePicture"
          :alt="`${user.name} profile picture`"
          size="lg"
          loading="eager" />
        <div class="min-w-0">
          <h1 class="truncate text-2xl font-semibold text-white">{{ user.name }}</h1>
          <p class="truncate text-sm text-muted-foreground">@{{ user.username }}</p>
          <p class="mt-1 text-sm text-muted-foreground">
            Member since {{ user.createdAtFormatted }}
            <template v-if="user.githubUsername">
              · <a
                class="text-primary hover:underline"
                :href="`https://github.com/${user.githubUsername}`"
                target="_blank"
                rel="noopener noreferrer">GitHub Profile</a>
            </template>
          </p>
        </div>
      </div>

      <div class="flex shrink-0">
        <Button
          v-if="user.canUpdate"
          :as="Link"
          :href="user.routes.edit"
          variant="secondary"
          size="sm">Edit Profile</Button>
      </div>
    </CardHeader>

    <CardContent>
      <section
        class="border-l-2 border-primary pl-4"
        aria-label="Member bio">
        <MarkdownContent
          v-if="user.bioHtml"
          :html="user.bioHtml" />
        <p
          v-else
          class="mb-0 text-muted-foreground">This member has not added a bio yet.</p>
      </section>
    </CardContent>
  </Card>
</template>
