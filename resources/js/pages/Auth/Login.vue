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
    <CardHeader><h1 class="text-2xl font-semibold text-white">Login</h1></CardHeader>
    <CardContent>
      <form method="POST" :action="session.routes.login" class="flex flex-col gap-4">
        <CsrfField />
        <FormField id="username" label="Username">
          <template #default="{ invalid, describedBy }">
            <input id="username" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" name="username" autocomplete="username" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required autofocus>
          </template>
        </FormField>
        <FormField id="password" label="Password">
          <template #default="{ invalid, describedBy }">
            <input id="password" type="password" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" name="password" autocomplete="current-password" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
          </template>
        </FormField>
        <label class="flex items-center gap-2 text-sm">
          <input type="checkbox" name="remember" class="size-4 rounded border-input bg-background">
          Remember me
        </label>
        <div class="flex flex-wrap items-center gap-3">
          <Button type="submit">Login</Button>
          <Button as="a" :href="session.routes.passwordRequest" variant="link">Forgot your password?</Button>
        </div>
      </form>
    </CardContent>
  </Card>
</template>
