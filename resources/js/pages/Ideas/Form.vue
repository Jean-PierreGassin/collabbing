<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { useSessionStore } from '@/stores/session';
import type { Idea } from '@/types/domain';

const props = defineProps<{
  idea?: Idea;
}>();

const session = useSessionStore();
</script>

<template>
  <Card>
    <CardHeader><h1 class="text-2xl font-semibold text-white">{{ idea ? 'Edit' : 'Share' }} your Idea</h1></CardHeader>
    <CardContent>
      <form :action="idea ? idea.routes.update : session.routes.ideasStore" method="POST" class="flex flex-col gap-5">
        <CsrfField />
        <MethodField v-if="idea" method="PUT" />

        <div class="grid gap-4 md:grid-cols-2">
          <FormField id="title" label="Title">
            <template #default="{ invalid, describedBy }">
              <input id="title" name="title" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" :value="idea?.title ?? ''" placeholder="A faster way to match design reviewers" maxlength="100" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
            </template>
          </FormField>

          <FormField id="communication" label="Communication">
            <template #default="{ invalid, describedBy }">
              <input id="communication" name="communication" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" :value="idea?.communication ?? ''" placeholder="Slack, Discord, email..." maxlength="50" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
            </template>
          </FormField>
        </div>

        <FormField id="repository_name" label="Repository Name" help="Use up to 50 letters, numbers, dashes, or underscores.">
          <template #default="{ invalid, describedBy }">
            <input id="repository_name" name="repository_name" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" :value="idea?.repositoryName ?? ''" placeholder="design-review-matchmaker" maxlength="50" pattern="[A-Za-z0-9_-]+" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>
          </template>
        </FormField>

        <FormField id="content" label="The Pitch (supports markdown)" help="Make it meaningful and to the point, short and sweet is the best way to get an idea across.">
          <template #default="{ invalid, describedBy }">
            <textarea id="content" name="content" class="min-h-40 rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" placeholder="Explain the problem, who it helps, and what a first version should do." maxlength="1500" :aria-invalid="invalid || undefined" :aria-describedby="describedBy" required>{{ idea?.content ?? '' }}</textarea>
          </template>
        </FormField>

        <div class="flex items-center justify-between gap-4">
          <label v-if="idea" for="status" class="flex items-center gap-2 text-sm">
            Status:
            <select id="status" name="status" class="h-9 rounded-md border border-input bg-background px-2 text-sm">
              <option value="open" :selected="idea.status === 'open'">Open</option>
              <option value="closed" :selected="idea.status === 'closed'">Closed</option>
            </select>
          </label>
          <span v-else />

          <Button type="submit" size="sm" :variant="idea ? 'secondary' : 'default'">
            {{ idea ? 'Edit Idea' : 'Share Idea' }}
          </Button>
        </div>
      </form>
    </CardContent>
  </Card>
</template>
