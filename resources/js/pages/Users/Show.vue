<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import type { DomainUser } from '@/types/domain';

defineProps<{
  user: DomainUser;
}>();
</script>

<template>
  <Card>
    <CardHeader>
      <h5 class="flex items-center justify-between gap-4 text-lg font-semibold">
        <a class="text-primary hover:underline" :href="user.routes.show">@{{ user.username }}</a>
        <Button v-if="user.canUpdate" as="a" :href="user.routes.edit" variant="secondary" size="sm">Edit Profile</Button>
      </h5>

      <h6 class="text-sm text-muted-foreground">
        Member since: {{ user.createdAtFormatted }}
        <template v-if="user.githubUsername">
          - <a class="text-primary hover:underline" :href="`https://github.com/${user.githubUsername}`">GitHub Profile</a>
        </template>
      </h6>
    </CardHeader>

    <CardContent>
      <blockquote class="border-l-2 border-primary pl-4">
        <p v-if="user.bioHtml" class="mb-0" v-html="user.bioHtml" />
        <p v-else class="mb-0">I've got nothing good to say</p>
        <footer class="mt-2 text-sm text-muted-foreground">
          Someone called
          <cite :title="`${user.firstName} ${user.lastName}`">
            {{ user.firstName }} {{ user.lastName }}
          </cite>
        </footer>
      </blockquote>
    </CardContent>
  </Card>
</template>
