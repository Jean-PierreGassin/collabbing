<script setup lang="ts">
import { computed, ref } from 'vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import FormSelect from '@/components/forms/FormSelect.vue';
import MethodField from '@/components/forms/MethodField.vue';
import PageHeader from '@/components/navigation/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { oldInputString } from '@/lib/forms';
import { maxLengthValidator } from '@/lib/formValidation';
import type { Idea, IdeaApplication } from '@/types/domain';

const props = defineProps<{
  idea: Idea;
  application?: IdeaApplication | null;
}>();

const contributionType = ref(oldInputString('contribution_type', props.application?.contributionType));
const firstAction = ref(oldInputString('first_action', props.application?.firstAction));
const applicationValidator = maxLengthValidator(1500, 'an application');
const firstActionValidator = maxLengthValidator(280, 'a first action');
const contributionOptions = [
  {
    label: 'Choose a contribution type',
    value: '',
  },
  {
    label: 'Frontend',
    value: 'frontend',
  },
  {
    label: 'Backend',
    value: 'backend',
  },
  {
    label: 'Design',
    value: 'design',
  },
  {
    label: 'Product',
    value: 'product',
  },
  {
    label: 'Testing',
    value: 'testing',
  },
  {
    label: 'DevOps',
    value: 'devops',
  },
  {
    label: 'Writing',
    value: 'writing',
  },
  {
    label: 'Research',
    value: 'research',
  },
  {
    label: 'Feedback',
    value: 'feedback',
  },
  {
    label: 'Marketing',
    value: 'marketing',
  },
  {
    label: 'I can help with anything',
    value: 'anything',
  },
];
const recommendedHelp = computed(() => new Set(props.idea.collaboration.helpWanted));
const recommendedHelpLabels = computed(() => props.idea.collaboration.helpWantedDisplay);
const pageTitle = computed(() => {
  if (props.application) {
    return `Edit application to ${props.idea.title}`;
  }

  return `Apply to ${props.idea.title}`;
});
const formAction = computed(() => props.application?.routes.update ?? props.idea.routes.applicationsStore);
const submitLabel = computed(() => props.application ? 'Update application' : 'Submit application');
</script>

<template>
  <section class="flex flex-col gap-5">
    <PageHeader :title="pageTitle" />

    <Card class="w-full max-w-3xl">
      <CardContent>
        <form
          :action="formAction"
          method="POST"
          class="flex flex-col gap-4">
          <CsrfField />
          <MethodField
            v-if="application"
            method="PUT" />
          <div
            v-if="recommendedHelpLabels.length > 0"
            class="flex flex-col gap-2 rounded-md border border-border bg-background/35 p-3 text-sm">
            <p class="font-medium text-foreground">
              Owner is looking for
            </p>
            <div class="flex flex-wrap gap-1.5">
              <Badge
                v-for="label in recommendedHelpLabels"
                :key="label"
                variant="secondary">
                {{ label }}
              </Badge>
            </div>
          </div>

          <FormField
            id="contribution_type"
            label="Contribution type"
            help="Pick the closest fit. Owner-requested areas are marked as recommended.">
            <template #default="{ describedBy, feedbackClass }">
              <FormSelect
                id="contribution_type"
                v-model="contributionType"
                name="contribution_type"
                required
                :class="feedbackClass"
                :aria-describedby="describedBy">
                <option
                  v-for="option in contributionOptions"
                  :key="option.value"
                  :value="option.value">
                  {{ option.label }}{{ recommendedHelp.has(option.value) ? ' - recommended' : '' }}
                </option>
              </FormSelect>
            </template>
          </FormField>

          <FormField
            id="first_action"
            label="First action"
            help="Name one useful thing you can do first."
            :validator="firstActionValidator">
            <template #default="{ invalid, describedBy, feedbackClass }">
              <input
                id="first_action"
                v-model="firstAction"
                name="first_action"
                type="text"
                :class="[
                  'h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                  feedbackClass,
                ]"
                placeholder="Review the first issue and suggest a small fix."
                maxlength="280"
                :aria-invalid="invalid || undefined"
                :aria-describedby="describedBy"
                required>
            </template>
          </FormField>

          <FormField
            id="content"
            label="Optional context"
            help="Share extra skills, context, or availability. Markdown is supported."
            :validator="applicationValidator">
            <template #default="{ invalid, describedBy, feedbackClass }">
              <textarea
                id="content"
                name="content"
                :class="[
                  'min-h-40 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40',
                  feedbackClass,
                ]"
                placeholder="I can help with backend APIs and weekly planning."
                maxlength="1500"
                :defaultValue="oldInputString('content', application?.content)"
                :aria-invalid="invalid || undefined"
                :aria-describedby="describedBy"
              />
            </template>
          </FormField>
          <div class="flex justify-end">
            <Button type="submit">
              {{ submitLabel }}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </section>
</template>
