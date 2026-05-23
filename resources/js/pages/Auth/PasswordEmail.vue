<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { oldInputString } from '@/lib/forms';
import { emailValidator } from '@/lib/formValidation';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();
</script>

<template>
  <Card class="mx-auto w-full max-w-md">
    <CardHeader>
      <h1 class="text-2xl font-semibold text-white">
        Reset password
      </h1>
    </CardHeader>
    <CardContent>
      <form
        method="POST"
        :action="session.routes.passwordEmail"
        class="flex flex-col gap-4">
        <CsrfField />
        <FormField
          id="email"
          label="Email address"
          :validator="emailValidator">
          <template #default="{ invalid, describedBy, feedbackClass }">
            <input
              id="email"
              type="email"
              :class="[
                'h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                feedbackClass,
              ]"
              name="email"
              autocomplete="email"
              maxlength="255"
              :defaultValue="oldInputString('email')"
              :aria-invalid="invalid || undefined"
              :aria-describedby="describedBy"
              required>
          </template>
        </FormField>
        <div class="flex justify-end">
          <Button type="submit">
            Send password reset link
          </Button>
        </div>
      </form>
    </CardContent>
  </Card>
</template>
