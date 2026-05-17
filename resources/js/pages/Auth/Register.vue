<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();
</script>

<template>
  <Card>
    <CardHeader><h1 class="text-2xl font-semibold text-white">Register</h1></CardHeader>
    <CardContent>
      <form method="POST" :action="session.routes.register" class="flex flex-col gap-4">
        <CsrfField />
        <FormField id="username" label="Username" help="Use 3-20 letters, numbers, dashes, or underscores.">
          <template #default="{ invalid, describedBy }">
            <input id="username" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" name="username" autocomplete="username" minlength="3" maxlength="20" pattern="[A-Za-z0-9_-]+" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required autofocus>
          </template>
        </FormField>
        <FormField id="first_name" label="First name">
          <template #default="{ invalid, describedBy }">
            <input id="first_name" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" name="first_name" autocomplete="given-name" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
          </template>
        </FormField>
        <FormField id="last_name" label="Last name">
          <template #default="{ invalid, describedBy }">
            <input id="last_name" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" name="last_name" autocomplete="family-name" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
          </template>
        </FormField>
        <FormField id="email" label="Email address">
          <template #default="{ invalid, describedBy }">
            <input id="email" type="email" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" name="email" autocomplete="email" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
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
        <Button type="submit" class="self-start">Register</Button>
      </form>
    </CardContent>
  </Card>
</template>
