<script setup lang="ts">
import { ref } from 'vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import IdeaSidebar from '@/components/ideas/IdeaSidebar.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import type { Idea, IdeaApplication } from '@/types/domain';

defineProps<{
  idea: Idea;
  applications: IdeaApplication[];
  collaborators: IdeaApplication[];
}>();

const activeTab = ref<'applications' | 'collaborators'>('applications');
</script>

<template>
  <section class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
    <div class="flex flex-col gap-4">
      <div class="flex items-center justify-between gap-4">
        <h4 class="text-xl font-semibold">{{ idea.title }}</h4>
      </div>

      <div class="flex flex-wrap gap-2">
        <Button :variant="activeTab === 'applications' ? 'default' : 'ghost'" @click="activeTab = 'applications'">Applications</Button>
        <Button :variant="activeTab === 'collaborators' ? 'default' : 'ghost'" @click="activeTab = 'collaborators'">Collaborators</Button>
      </div>

      <div v-if="activeTab === 'applications'" class="flex flex-col gap-3">
        <template v-if="applications.length > 0">
          <Card v-for="application in applications" :key="application.id">
            <CardHeader>
              <h6 class="font-medium">
                <a class="text-primary hover:underline" :href="application.user.routes.show">{{ application.user.name }}'s Application</a>
              </h6>
            </CardHeader>
            <CardContent class="flex flex-col gap-4">
              <div class="whitespace-pre-line">{{ application.content }}</div>
              <h6 class="text-right text-sm text-muted-foreground">Submitted {{ idea.createdAtForHumans }}</h6>
              <div class="flex flex-wrap justify-between gap-3 border-t border-border pt-4">
                <form v-if="idea.can.deleteApplication" :action="application.routes.destroy" method="POST">
                  <CsrfField />
                  <MethodField method="DELETE" />
                  <Button type="submit" variant="destructive" size="sm">Decline this Application 👎</Button>
                </form>
                <form v-if="idea.can.updateApplication" :action="application.routes.approve" method="POST">
                  <CsrfField />
                  <MethodField method="PUT" />
                  <Button type="submit" size="sm">Approve this Application ✅</Button>
                </form>
              </div>
            </CardContent>
          </Card>
        </template>
        <i v-else>~ tumbleweed</i>
      </div>

      <div v-else class="flex flex-col gap-3">
        <template v-if="collaborators.length > 0">
          <div v-for="collaborator in collaborators" :key="collaborator.id" class="flex items-center justify-between gap-4 rounded-md border border-border bg-card px-4 py-3">
            <a class="text-primary hover:underline" :href="collaborator.user.routes.show">
              {{ collaborator.user.firstName }} {{ collaborator.user.lastName }}
            </a>
            <form v-if="idea.can.deleteApplication" :action="collaborator.routes.destroy" method="POST">
              <CsrfField />
              <MethodField method="DELETE" />
              <Button type="submit" variant="destructive" size="sm">Remove Collaborator 🤕</Button>
            </form>
          </div>
        </template>
        <p v-else>Looks a little lonely in here 😰</p>
      </div>
    </div>

    <aside class="flex flex-col gap-3">
      <div class="flex flex-wrap gap-2">
        <Button as="a" :href="idea.routes.edit" variant="secondary">Edit Idea</Button>
        <Button v-if="!idea.repository" as="a" :href="idea.routes.repositoryCreate" variant="outline">Create Repository</Button>
        <Button v-else as="a" :href="idea.routes.repositoryInvite" variant="outline">Invite Collaborators</Button>
      </div>
      <IdeaSidebar :idea="idea" />
    </aside>
  </section>
</template>
