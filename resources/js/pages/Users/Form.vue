<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { oldInputString } from '@/lib/forms';
import type { DomainUser } from '@/types/domain';

const props = defineProps<{
  user?: DomainUser;
  githubClientId?: string | null;
}>();

let pageTitle = 'Create profile';
let submitLabel = 'Create Profile';

if (props.user) {
  pageTitle = 'Edit profile';
  submitLabel = 'Edit Profile';
}

function submitProfile(event: SubmitEvent): void {
  if (!props.user?.routes.update) {
    return;
  }

  router.post(props.user.routes.update, new FormData(event.currentTarget as HTMLFormElement), {
    preserveScroll: true,
  });
}
</script>

<template>
  <Card>
    <CardHeader><h1 class="text-2xl font-semibold text-white">{{ pageTitle }}</h1></CardHeader>
    <CardContent>
      <form :action="user?.routes.update" method="POST" class="flex flex-col gap-5" @submit.prevent="submitProfile">
        <CsrfField />
        <MethodField v-if="user" method="PUT" />

        <div class="grid gap-4 md:grid-cols-2">
          <FormField id="first_name" label="First name">
            <template #default="{ invalid, describedBy }">
              <input id="first_name" name="first_name" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" :value="oldInputString('first_name', user?.firstName)" placeholder="John" autocomplete="given-name" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
            </template>
          </FormField>
          <FormField id="last_name" label="Last name">
            <template #default="{ invalid, describedBy }">
              <input id="last_name" name="last_name" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" :value="oldInputString('last_name', user?.lastName)" placeholder="Smith" autocomplete="family-name" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
            </template>
          </FormField>
        </div>

        <FormField id="email" label="Email address">
          <template #default="{ invalid, describedBy }">
            <input id="email" name="email" type="email" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" :value="oldInputString('email', user?.email)" placeholder="john.smith@apples.com" autocomplete="email" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
          </template>
        </FormField>

        <div v-if="user" class="flex flex-col gap-3">
          <h4 class="text-xl font-semibold">Integrations</h4>
          <p class="text-sm text-muted-foreground">
            Link GitHub to create repositories from ideas and invite approved collaborators.
            <a class="text-primary hover:underline" :href="`https://github.com/settings/connections/applications/${githubClientId ?? ''}`" target="_blank" rel="noopener noreferrer">Review GitHub access</a>
          </p>
          <Button v-if="user.hasGithubToken" type="submit" form="github-revoke-form" variant="outline" size="sm" class="w-fit">
            Unlink GitHub
          </Button>
          <Button v-else as="a" :href="user.routes.githubLogin" variant="outline" size="sm" class="w-fit">
            Link GitHub
          </Button>
        </div>

        <FormField id="bio" label="Bio (supports markdown)">
          <template #default="{ invalid, describedBy }">
            <textarea id="bio" name="bio" class="min-h-32 rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" placeholder="Tell us what you're good at and what you enjoy..." maxlength="500" :value="oldInputString('bio', user?.bio)" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" />
          </template>
        </FormField>

        <div class="grid gap-4 md:grid-cols-2">
          <FormField id="password" label="New password" help="Leave blank to keep your current password. New passwords need at least 12 characters with letters and numbers.">
            <template #default="{ invalid, describedBy }">
              <input id="password" name="password" type="password" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" autocomplete="new-password" minlength="12" maxlength="128" :aria-invalid="invalid || undefined" :aria-describedby="describedBy">
            </template>
          </FormField>
          <FormField id="password_confirmation" label="Confirm password">
            <template #default="{ invalid, describedBy }">
              <input id="password_confirmation" name="password_confirmation" type="password" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" autocomplete="new-password" minlength="12" maxlength="128" :aria-invalid="invalid || undefined" :aria-describedby="describedBy">
            </template>
          </FormField>
        </div>

        <Button type="submit" class="self-start">{{ submitLabel }}</Button>
      </form>
      <form v-if="user?.hasGithubToken" id="github-revoke-form" :action="user.routes.githubRevoke" method="POST" class="hidden">
        <CsrfField />
        <MethodField method="DELETE" />
      </form>
    </CardContent>
  </Card>
</template>
