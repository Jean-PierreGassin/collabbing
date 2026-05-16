<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import type { DomainUser } from '@/types/domain';

defineProps<{
  user?: DomainUser;
  githubClientId?: string | null;
}>();
</script>

<template>
  <Card>
    <CardHeader>{{ user ? 'Edit' : 'Create' }} Profile</CardHeader>
    <CardContent>
      <form :action="user?.routes.update" method="POST" class="flex flex-col gap-5">
        <CsrfField />
        <MethodField v-if="user" method="PUT" />

        <div class="grid gap-4 md:grid-cols-2">
          <FormField id="first_name" label="First Name">
            <input id="first_name" name="first_name" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" :value="user?.firstName ?? ''" placeholder="John" required>
          </FormField>
          <FormField id="last_name" label="Last Name">
            <input id="last_name" name="last_name" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" :value="user?.lastName ?? ''" placeholder="Smith" required>
          </FormField>
        </div>

        <FormField id="email" label="E-Mail Address">
          <input id="email" name="email" type="email" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" :value="user?.email ?? ''" placeholder="john.smith@apples.com" required>
        </FormField>

        <div v-if="user" class="flex flex-col gap-3">
          <h4 class="text-xl font-semibold">Integrations</h4>
          <p class="text-sm text-muted-foreground">
            Linking your GitHub account will allow for seamless integration between your ideas and repositories - view your access to Collabbing
            <a class="text-primary hover:underline" :href="`https://github.com/settings/connections/applications/${githubClientId ?? ''}`">here</a>
          </p>
          <Button as="a" :href="user.hasGithubToken ? user.routes.githubRevoke : user.routes.githubLogin" :variant="user.hasGithubToken ? 'default' : 'outline'" size="sm" class="w-fit">
            {{ user.hasGithubToken ? 'Un-link GitHub' : 'Link GitHub' }}
          </Button>
        </div>

        <FormField id="bio" label="Bio (supports markdown)">
          <textarea id="bio" name="bio" class="min-h-32 rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" placeholder="Tell us what you're good at and what you enjoy...">{{ user?.bio ?? '' }}</textarea>
        </FormField>

        <div class="grid gap-4 md:grid-cols-2">
          <FormField id="password" label="New Password:">
            <input id="password" name="password" type="password" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40">
          </FormField>
          <FormField id="password_confirmation" label="Confirm Password:">
            <input id="password_confirmation" name="password_confirmation" type="password" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40">
          </FormField>
        </div>

        <Button type="submit" class="self-start">{{ user ? 'Edit Profile' : 'Create Profile' }}</Button>
      </form>
    </CardContent>
  </Card>
</template>
