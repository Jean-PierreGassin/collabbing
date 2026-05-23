<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { oldInputString } from '@/lib/forms';
import { emailValidator, passwordConfirmationValidator, passwordValidator } from '@/lib/formValidation';
import { useSessionStore } from '@/stores/session';

defineProps<{
  token: string | null;
  email: string | null;
}>();

const session = useSessionStore();
</script>

<template>
  <Card class="mx-auto w-full max-w-2xl">
    <CardHeader>
      <h1 class="text-2xl font-semibold text-white">
        Reset password
      </h1>
    </CardHeader>
    <CardContent>
      <form
        method="POST"
        :action="session.routes.passwordRequest"
        class="flex flex-col gap-4">
        <CsrfField />
        <input
          type="hidden"
          name="token"
          :value="token ?? ''">
        <FormField
          id="email"
          label="Email address"
          :validator="emailValidator">
          <template #default="{ invalid, describedBy, feedbackClass }">
            <input
              id="email"
              type="email"
              :class="['h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]"
              name="email"
              :defaultValue="oldInputString('email', email)"
              autocomplete="email"
              maxlength="255"
              :aria-invalid="invalid || undefined"
              :aria-describedby="describedBy"
              required
              autofocus>
          </template>
        </FormField>
        <div class="grid gap-4 sm:grid-cols-2">
          <FormField
            id="password"
            label="Password"
            help="Use at least 12 characters with letters and numbers."
            :validator="passwordValidator">
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
            :validator="passwordConfirmationValidator">
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
          <Button type="submit">
            Reset password
          </Button>
        </div>
      </form>
    </CardContent>
  </Card>
</template>
