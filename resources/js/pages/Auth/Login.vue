<script setup lang="ts">
import AuthMorphCard from '@/components/auth/AuthMorphCard.vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import { Button } from '@/components/ui/button';
import { oldInputBoolean, oldInputString } from '@/lib/forms';
import { loginPasswordValidator, loginUsernameValidator } from '@/lib/formValidation';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();
</script>

<template>
  <AuthMorphCard
    title="Login"
    :action="session.routes.login"
    :success-href="session.routes.ideas">
    <template #default="{ errorsFor, isSubmitting }">
      <CsrfField />
      <FormField
        id="username"
        label="Username"
        :validator="loginUsernameValidator"
        :external-errors="errorsFor('username')">
        <template #default="{ invalid, describedBy, feedbackClass }">
          <input
            id="username"
            type="text"
            :class="[
              'h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
              feedbackClass,
            ]"
            name="username"
            autocomplete="username"
            maxlength="20"
            :defaultValue="oldInputString('username')"
            :aria-invalid="invalid || undefined"
            :aria-describedby="describedBy"
            required
            autofocus>
        </template>
      </FormField>
      <FormField
        id="password"
        label="Password"
        :validator="loginPasswordValidator"
        :external-errors="errorsFor('password')">
        <template #default="{ invalid, describedBy, feedbackClass }">
          <input
            id="password"
            type="password"
            :class="[
              'h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
              feedbackClass,
            ]"
            name="password"
            autocomplete="current-password"
            maxlength="128"
            :aria-invalid="invalid || undefined"
            :aria-describedby="describedBy"
            required>
        </template>
      </FormField>
      <label class="flex items-center gap-2 text-sm">
        <input
          type="checkbox"
          name="remember"
          class="size-4 rounded border-input bg-background"
          :checked="oldInputBoolean('remember')">
        Remember me
      </label>
      <div class="flex flex-wrap items-center justify-end gap-3">
        <Button
          as="a"
          :href="session.routes.passwordRequest"
          variant="link">
          Forgot your password?
        </Button>
        <Button
          type="submit"
          :disabled="isSubmitting">
          {{ isSubmitting ? 'Checking...' : 'Login' }}
        </Button>
      </div>
    </template>
  </AuthMorphCard>
</template>
