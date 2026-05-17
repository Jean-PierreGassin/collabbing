<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { useSessionStore } from '@/stores/session';

defineProps<{
  token: string | null;
  email: string | null;
}>();

const session = useSessionStore();
</script>

<template>
  <Card class="mx-auto w-full max-w-3xl">
    <CardHeader><h1 class="text-2xl font-semibold text-white">Reset password</h1></CardHeader>
    <CardContent>
      <form method="POST" :action="session.routes.passwordRequest" class="flex flex-col gap-4">
        <CsrfField />
        <input type="hidden" name="token" :value="token ?? ''">
        <FormField id="email" label="Email address">
          <template #default="{ invalid, describedBy }">
            <input id="email" type="email" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" name="email" :value="email ?? ''" autocomplete="email" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required autofocus>
          </template>
        </FormField>
        <FormField id="password" label="Password" help="Use at least 12 characters with letters and numbers.">
          <template #default="{ invalid, describedBy }">
            <input id="password" type="password" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" name="password" autocomplete="new-password" minlength="12" maxlength="128" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
          </template>
        </FormField>
        <FormField id="password_confirmation" label="Confirm password">
          <template #default="{ invalid, describedBy }">
            <input id="password_confirmation" type="password" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" name="password_confirmation" autocomplete="new-password" minlength="12" maxlength="128" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
          </template>
        </FormField>
        <Button type="submit" class="self-start">Reset password</Button>
      </form>
    </CardContent>
  </Card>
</template>
