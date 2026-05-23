<script setup lang="ts">
import { computed } from 'vue';
import AuthMorphCard from '@/components/auth/AuthMorphCard.vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import { Button } from '@/components/ui/button';
import { oldInputString } from '@/lib/forms';
import {
  emailValidator,
  passwordConfirmationValidator,
  passwordValidator,
  personNameValidator,
  usernameValidator,
} from '@/lib/formValidation';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();

const successHref = computed(() => {
  const next = new URLSearchParams(window.location.search).get('next');

  if (next === session.routes.ideasCreate) {
    return next;
  }

  return session.routes.ideas;
});
</script>

<template>
  <AuthMorphCard
    title="Register"
    :action="session.routes.register"
    :success-href="successHref"
    max-width="lg">
    <template #default="{ errorsFor, isSubmitting }">
      <CsrfField />
      <FormField
        id="username"
        label="Username"
        help="Use 3-20 letters, numbers, dashes, or underscores."
        :validator="usernameValidator"
        :external-errors="errorsFor('username')">
        <template #default="{ invalid, describedBy, feedbackClass }">
          <input
            id="username"
            type="text"
            :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]"
            name="username"
            autocomplete="username"
            minlength="3"
            maxlength="20"
            pattern="[A-Za-z0-9_-]+"
            :defaultValue="oldInputString('username')"
            :aria-invalid="invalid || undefined"
            :aria-describedby="describedBy"
            required
            autofocus>
        </template>
      </FormField>
      <div class="grid gap-4 sm:grid-cols-2">
        <FormField
          id="first_name"
          label="First name"
          :validator="personNameValidator"
          :external-errors="errorsFor('first_name')">
          <template #default="{ invalid, describedBy, feedbackClass }">
            <input
              id="first_name"
              type="text"
              :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]"
              name="first_name"
              autocomplete="given-name"
              maxlength="255"
              :defaultValue="oldInputString('first_name')"
              :aria-invalid="invalid || undefined"
              :aria-describedby="describedBy"
              required>
          </template>
        </FormField>
        <FormField
          id="last_name"
          label="Last name"
          :validator="personNameValidator"
          :external-errors="errorsFor('last_name')">
          <template #default="{ invalid, describedBy, feedbackClass }">
            <input
              id="last_name"
              type="text"
              :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]"
              name="last_name"
              autocomplete="family-name"
              maxlength="255"
              :defaultValue="oldInputString('last_name')"
              :aria-invalid="invalid || undefined"
              :aria-describedby="describedBy"
              required>
          </template>
        </FormField>
      </div>
      <FormField
        id="email"
        label="Email address"
        :validator="emailValidator"
        :external-errors="errorsFor('email')">
        <template #default="{ invalid, describedBy, feedbackClass }">
          <input
            id="email"
            type="email"
            :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]"
            name="email"
            autocomplete="email"
            maxlength="255"
            :defaultValue="oldInputString('email')"
            :aria-invalid="invalid || undefined"
            :aria-describedby="describedBy"
            required>
        </template>
      </FormField>
      <div class="grid gap-4 sm:grid-cols-2">
        <FormField
          id="password"
          label="Password"
          help="Use at least 12 characters with letters and numbers."
          :validator="passwordValidator"
          :external-errors="errorsFor('password')">
          <template #default="{ invalid, describedBy, feedbackClass }">
            <input
              id="password"
              type="password"
              :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]"
              name="password"
              autocomplete="new-password"
              minlength="12"
              maxlength="128"
              :aria-invalid="invalid || undefined"
              :aria-describedby="describedBy"
              required>
          </template>
        </FormField>
        <FormField
          id="password_confirmation"
          label="Confirm password"
          :validator="passwordConfirmationValidator"
          :external-errors="errorsFor('password_confirmation')">
          <template #default="{ invalid, describedBy, feedbackClass }">
            <input
              id="password_confirmation"
              type="password"
              :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]"
              name="password_confirmation"
              autocomplete="new-password"
              minlength="12"
              maxlength="128"
              :aria-invalid="invalid || undefined"
              :aria-describedby="describedBy"
              required>
          </template>
        </FormField>
      </div>
      <div class="flex justify-end">
        <Button
          type="submit"
          :disabled="isSubmitting">
          {{ isSubmitting ? 'Creating...' : 'Register' }}
        </Button>
      </div>
    </template>
  </AuthMorphCard>
</template>
