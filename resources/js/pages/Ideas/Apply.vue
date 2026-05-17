<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import PageHeader from '@/components/navigation/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { oldInputString } from '@/lib/forms';
import type { Idea } from '@/types/domain';

defineProps<{
  idea: Idea;
}>();
</script>

<template>
  <section class="flex flex-col gap-5">
    <PageHeader :title="`Apply to ${idea.title}`" />

    <Card>
      <CardContent>
        <form :action="idea.routes.applicationsStore" method="POST" class="flex flex-col gap-4">
          <CsrfField />
          <FormField id="content" label="Application" help="Share the skills, context, or time you can contribute. Markdown is supported.">
            <template #default="{ invalid, describedBy }">
              <textarea
                id="content"
                name="content"
                class="min-h-40 rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40"
                placeholder="I can help with backend APIs and weekly planning."
                maxlength="1500"
                :value="oldInputString('content')"
                :aria-invalid="invalid || undefined"
                :aria-describedby="describedBy"
                required
              />
            </template>
          </FormField>
          <Button type="submit" class="self-start">Submit Application</Button>
        </form>
      </CardContent>
    </Card>
  </section>
</template>
