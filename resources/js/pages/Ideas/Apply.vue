<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import type { Idea } from '@/types/domain';

defineProps<{
  idea: Idea;
}>();
</script>

<template>
  <Card>
    <CardHeader><h5 class="text-lg font-semibold">{{ idea.title }}</h5></CardHeader>
    <CardContent>
      <form :action="idea.routes.applicationsStore" method="POST" class="flex flex-col gap-4">
        <CsrfField />
        <FormField id="content" label="Application" help="Tell us why you're good for the part, make it interesting and we'll do the rest.">
          <textarea
            id="content"
            name="content"
            class="min-h-40 rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40"
            placeholder="I'm really good at water sports even though it has nothing to do with this project."
            required
          />
        </FormField>
        <Button type="submit" variant="outline" class="self-start">Submit Application 😎</Button>
      </form>
    </CardContent>
  </Card>
</template>
