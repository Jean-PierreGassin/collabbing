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
    <CardHeader>{{ idea ? 'Edit' : 'Share' }} your Idea</CardHeader>
    <CardContent>
      <form :action="idea ? idea.routes.update : session.routes.ideasStore" method="POST" class="flex flex-col gap-5">
        <CsrfField />
        <MethodField v-if="idea" method="PUT" />

        <div class="grid gap-4 md:grid-cols-2">
          <FormField id="title" label="Title">
            <input id="title" name="title" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" :value="idea?.title ?? ''" placeholder="A kettle that comes to you!" required>
          </FormField>

          <FormField id="communication" label="Communication">
            <input id="communication" name="communication" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" :value="idea?.communication ?? ''" placeholder="e.g Slack, Telegram, KettleChat..." required>
          </FormField>
        </div>

        <FormField id="repository_name" label="Repository Name" help="This will be the name of your repository once you're ready to create it.">
          <input id="repository_name" name="repository_name" type="text" class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" :value="idea?.repositoryName ?? ''" placeholder="kettle-catastrophe" required>
        </FormField>

        <FormField id="content" label="The Pitch (supports markdown)" help="Make it meaningful and to the point, short and sweet is the best way to get an idea across.">
          <textarea id="content" name="content" class="min-h-40 rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40" placeholder="A platform that brings people together to create bangin' ideas." required>{{ idea?.content ?? '' }}</textarea>
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
            {{ idea ? 'Edit Idea 💡' : 'Share Idea 💡' }}
          </Button>
        </div>
      </form>
    </CardContent>
  </Card>
</template>
