<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import PageHeader from '@/components/navigation/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { oldInputString } from '@/lib/forms';
import { maxLengthValidator } from '@/lib/formValidation';
import type { Idea } from '@/types/domain';

defineProps<{
  idea: Idea;
}>();

const applicationValidator = maxLengthValidator(1500, 'an application');
</script>

<template>
  <section class="flex flex-col gap-5">
    <PageHeader :title="`Apply to ${idea.title}`" />

    <Card class="w-full max-w-3xl">
      <CardContent>
        <form :action="idea.routes.applicationsStore" method="POST" class="flex flex-col gap-4">
          <CsrfField />
          <FormField id="content" label="Application" help="Share the skills, context, or time you can contribute. Markdown is supported." :validator="applicationValidator">
            <template #default="{ invalid, describedBy, feedbackClass }">
              <textarea
                id="content"
                name="content"
                :class="['min-h-40 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40', feedbackClass]"
                placeholder="I can help with backend APIs and weekly planning."
                maxlength="1500"
                :defaultValue="oldInputString('content')"
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
