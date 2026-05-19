<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
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
</script>

<template>
  <Card class="mx-auto w-full max-w-2xl">
    <CardHeader><h1 class="text-2xl font-semibold text-white">Register</h1></CardHeader>
    <CardContent>
      <form method="POST" :action="session.routes.register" class="flex flex-col gap-4">
        <CsrfField />
        <FormField id="username" label="Username" help="Use 3-20 letters, numbers, dashes, or underscores." :validator="usernameValidator">
          <template #default="{ invalid, describedBy, feedbackClass }">
            <input id="username" type="text" :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]" name="username" autocomplete="username" minlength="3" maxlength="20" pattern="[A-Za-z0-9_-]+" :defaultValue="oldInputString('username')" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required autofocus>
          </template>
        </FormField>
        <div class="grid gap-4 sm:grid-cols-2">
          <FormField id="first_name" label="First name" :validator="personNameValidator">
            <template #default="{ invalid, describedBy, feedbackClass }">
              <input id="first_name" type="text" :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]" name="first_name" autocomplete="given-name" maxlength="255" :defaultValue="oldInputString('first_name')" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
            </template>
          </FormField>
          <FormField id="last_name" label="Last name" :validator="personNameValidator">
            <template #default="{ invalid, describedBy, feedbackClass }">
              <input id="last_name" type="text" :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]" name="last_name" autocomplete="family-name" maxlength="255" :defaultValue="oldInputString('last_name')" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
            </template>
          </FormField>
        </div>
        <FormField id="email" label="Email address" :validator="emailValidator">
          <template #default="{ invalid, describedBy, feedbackClass }">
            <input id="email" type="email" :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]" name="email" autocomplete="email" maxlength="255" :defaultValue="oldInputString('email')" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
          </template>
        </FormField>
        <div class="grid gap-4 sm:grid-cols-2">
          <FormField id="password" label="Password" help="Use at least 12 characters with letters and numbers." :validator="passwordValidator">
            <template #default="{ invalid, describedBy, feedbackClass }">
              <input id="password" type="password" :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]" name="password" autocomplete="new-password" minlength="12" maxlength="128" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
            </template>
          </FormField>
          <FormField id="password_confirmation" label="Confirm password" :validator="passwordConfirmationValidator">
            <template #default="{ invalid, describedBy, feedbackClass }">
              <input id="password_confirmation" type="password" :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]" name="password_confirmation" autocomplete="new-password" minlength="12" maxlength="128" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
            </template>
          </FormField>
        </div>
        <div class="flex justify-end">
          <Button type="submit">Register</Button>
        </div>
      </form>
    </CardContent>
  </Card>
</template>
