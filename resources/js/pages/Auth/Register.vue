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
    <CardHeader>{{ 'Register' }}</CardHeader>
    <CardContent>
      <form method="POST" :action="session.routes.register" class="flex flex-col gap-4">
        <CsrfField />
        <FormField id="username" :label="'Username'">
          <input id="username" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" name="username" required autofocus>
        </FormField>
        <FormField id="first_name" :label="'First Name'">
          <input id="first_name" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" name="first_name" required autofocus>
        </FormField>
        <FormField id="last_name" :label="'Last Name'">
          <input id="last_name" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" name="last_name" required autofocus>
        </FormField>
        <FormField id="email" :label="'E-Mail Address'">
          <input id="email" type="email" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" name="email" required>
        </FormField>
        <FormField id="password" :label="'Password'">
          <input id="password" type="password" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" name="password" required>
        </FormField>
        <FormField id="password_confirmation" :label="'Confirm Password'">
          <input id="password_confirmation" type="password" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" name="password_confirmation" required>
        </FormField>
        <Button type="submit" class="self-start">Register</Button>
      </form>
    </CardContent>
  </Card>
</template>
