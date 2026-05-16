<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();
</script>

<template>
  <Card class="mx-auto w-full max-w-3xl">
    <CardHeader>{{ 'Reset Password' }}</CardHeader>
    <CardContent>
      <form method="POST" :action="session.routes.passwordEmail" class="flex flex-col gap-4">
        <CsrfField />
        <FormField id="email" :label="'E-Mail Address'">
          <input id="email" type="email" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" name="email" required>
        </FormField>
        <Button type="submit" class="self-start">Send Password Reset Link</Button>
      </form>
    </CardContent>
  </Card>
</template>
