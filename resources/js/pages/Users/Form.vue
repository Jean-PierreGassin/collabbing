<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { oldInputString } from '@/lib/forms';
import {
  emailValidator,
  optionalBioValidator,
  optionalPasswordConfirmationValidator,
  optionalPasswordValidator,
  personNameValidator,
} from '@/lib/formValidation';
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

  if (event.defaultPrevented) {
    return;
  }

  const form = event.currentTarget;

  if (!(form instanceof HTMLFormElement)) {
    return;
  }

  if (!form.checkValidity()) {
    form.reportValidity();

    return;
  }

  event.preventDefault();

  router.post(props.user.routes.update, new FormData(form), {
    preserveScroll: true,
  });
}
</script>

<template>
  <Card class="mx-auto w-full max-w-4xl">
    <CardHeader>
      <h1 class="text-2xl font-semibold text-white">
        {{ pageTitle }}
      </h1>
    </CardHeader>
    <CardContent>
      <form
        :action="user?.routes.update"
        method="POST"
        class="flex flex-col gap-5"
        @submit="submitProfile">
        <CsrfField />
        <MethodField
          v-if="user"
          method="PUT" />

        <div class="grid gap-4 md:grid-cols-2">
          <FormField
            id="first_name"
            label="First name"
            :validator="personNameValidator">
            <template #default="{ invalid, describedBy, feedbackClass }">
              <input
                id="first_name"
                name="first_name"
                type="text"
                :class="[
                  'h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                  feedbackClass,
                ]"
                :defaultValue="oldInputString('first_name', user?.firstName)"
                placeholder="John"
                autocomplete="given-name"
                maxlength="255"
                :aria-invalid="invalid || undefined"
                :aria-describedby="describedBy"
                required>
            </template>
          </FormField>
          <FormField
            id="last_name"
            label="Last name"
            :validator="personNameValidator">
            <template #default="{ invalid, describedBy, feedbackClass }">
              <input
                id="last_name"
                name="last_name"
                type="text"
                :class="[
                  'h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                  feedbackClass,
                ]"
                :defaultValue="oldInputString('last_name', user?.lastName)"
                placeholder="Smith"
                autocomplete="family-name"
                maxlength="255"
                :aria-invalid="invalid || undefined"
                :aria-describedby="describedBy"
                required>
            </template>
          </FormField>
        </div>

        <FormField
          id="email"
          label="Email address"
          :validator="emailValidator">
          <template #default="{ invalid, describedBy, feedbackClass }">
            <input
              id="email"
              name="email"
              type="email"
              :class="[
                'h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                feedbackClass,
              ]"
              :defaultValue="oldInputString('email', user?.email)"
              placeholder="john.smith@apples.com"
              autocomplete="email"
              maxlength="255"
              :aria-invalid="invalid || undefined"
              :aria-describedby="describedBy"
              required>
          </template>
        </FormField>

        <div
          v-if="user"
          class="flex flex-col gap-3">
          <h4 class="text-xl font-semibold">
            Integrations
          </h4>
          <p class="text-sm text-muted-foreground">
            Link GitHub to create repositories from ideas and invite approved collaborators.
            <a
              class="text-primary hover:underline"
              :href="`https://github.com/settings/connections/applications/${githubClientId ?? ''}`"
              target="_blank"
              rel="noopener noreferrer">Review GitHub access</a>
          </p>
          <Button
            v-if="user.hasGithubToken"
            type="submit"
            form="github-revoke-form"
            variant="outline"
            size="sm"
            class="w-fit">
            Unlink GitHub
          </Button>
          <Button
            v-else
            as="a"
            :href="user.routes.githubLogin"
            variant="outline"
            size="sm"
            class="w-fit">
            Link GitHub
          </Button>
        </div>

        <FormField
          id="bio"
          label="Bio (supports markdown)"
          :validator="optionalBioValidator">
          <template #default="{ invalid, describedBy, feedbackClass }">
            <textarea
              id="bio"
              name="bio"
              :class="[
                'min-h-32 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                feedbackClass,
              ]"
              placeholder="Tell us what you're good at and what you enjoy..."
              maxlength="500"
              :defaultValue="oldInputString('bio', user?.bio)"
              :aria-invalid="invalid || undefined"
              :aria-describedby="describedBy" />
          </template>
        </FormField>

        <div class="grid gap-4 md:grid-cols-2">
          <FormField
            id="password"
            label="New password"
            help="Leave blank to keep your current password. New passwords need at least 12 characters with letters and numbers."
            :validator="optionalPasswordValidator">
            <template #default="{ invalid, describedBy, feedbackClass }">
              <input
                id="password"
                name="password"
                type="password"
                :class="[
                  'h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                  feedbackClass,
                ]"
                autocomplete="new-password"
                minlength="12"
                maxlength="128"
                :aria-invalid="invalid || undefined"
                :aria-describedby="describedBy">
            </template>
          </FormField>
          <FormField
            id="password_confirmation"
            label="Confirm password"
            :validator="optionalPasswordConfirmationValidator">
            <template #default="{ invalid, describedBy, feedbackClass }">
              <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                :class="[
                  'h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                  feedbackClass,
                ]"
                autocomplete="new-password"
                minlength="12"
                maxlength="128"
                :aria-invalid="invalid || undefined"
                :aria-describedby="describedBy">
            </template>
          </FormField>
        </div>

        <div class="flex justify-end">
          <Button type="submit">
            {{ submitLabel }}
          </Button>
        </div>
      </form>
      <form
        v-if="user?.hasGithubToken"
        id="github-revoke-form"
        :action="user.routes.githubRevoke"
        method="POST"
        class="hidden">
        <CsrfField />
        <MethodField method="DELETE" />
      </form>
    </CardContent>
  </Card>
</template>
