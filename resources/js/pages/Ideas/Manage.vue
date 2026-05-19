<script setup lang="ts">
import { ref } from 'vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import IdeaSidebar from '@/components/ideas/IdeaSidebar.vue';
import PaginationLinks from '@/components/pagination/PaginationLinks.vue';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Link } from '@inertiajs/vue3';
import { GitBranch, Pencil, RefreshCw } from '@lucide/vue';
import type { Idea, IdeaApplication, Paginator } from '@/types/domain';

type ManageTab = 'applications' | 'collaborators';

defineProps<{
  idea: Idea;
  applications: Paginator<IdeaApplication>;
  collaborators: Paginator<IdeaApplication>;
}>();

function initialTab(): ManageTab {
  if (new URLSearchParams(window.location.search).has('collaborators')) {
    return 'collaborators';
  }

  return 'applications';
}

function tabVariant(tab: ManageTab): 'ghost' | 'secondary' {
  if (activeTab.value === tab) {
    return 'secondary';
  }

  return 'ghost';
}

const activeTab = ref<ManageTab>(initialTab());
</script>

<template>
  <section class="flex flex-col gap-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
      <div class="flex min-w-0 flex-col gap-1">
        <h1 class="text-2xl font-semibold leading-tight text-white">{{ idea.title }}</h1>
      </div>
      <div class="flex shrink-0 flex-wrap justify-end gap-2 sm:pt-0.5">
        <Button v-if="!idea.repository && !idea.user.hasGithubToken" as="a" :href="idea.routes.repositoryCreate" size="sm">
          <GitBranch class="size-4" aria-hidden="true" />
          Link GitHub to create a repository
        </Button>
        <form v-else-if="!idea.repository" :action="idea.routes.repositoryCreate" method="POST">
          <CsrfField />
          <Button type="submit" size="sm">
            <GitBranch class="size-4" aria-hidden="true" />
            Create Repository
          </Button>
        </form>
        <form v-else :action="idea.routes.repositoryInvite" method="POST">
          <CsrfField />
          <Button type="submit" size="sm">
            <GitBranch class="size-4" aria-hidden="true" />
            Invite Collaborators
          </Button>
        </form>
        <Button :as="Link" :href="idea.routes.edit" variant="outline" size="sm">
          <Pencil class="size-4" aria-hidden="true" />
          Edit
        </Button>
      </div>
    </header>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
      <div class="flex flex-col gap-4">
        <div class="flex flex-wrap gap-1 rounded-md border border-border bg-card p-1">
          <Button :variant="tabVariant('applications')" :aria-pressed="activeTab === 'applications'" @click="activeTab = 'applications'">Applications</Button>
          <Button :variant="tabVariant('collaborators')" :aria-pressed="activeTab === 'collaborators'" @click="activeTab = 'collaborators'">Collaborators</Button>
        </div>

        <Transition name="content-fade" mode="out-in">
          <div v-if="activeTab === 'applications'" key="applications" class="flex flex-col gap-3">
            <template v-if="applications.items.length > 0">
              <Card v-for="application in applications.items" :key="application.id">
                <CardHeader>
                  <h6 class="font-medium">
                    <a class="text-primary hover:underline" :href="application.user.routes.show">{{ application.user.name }}'s Application</a>
                  </h6>
                </CardHeader>
                <CardContent class="flex flex-col gap-4">
                  <MarkdownContent :html="application.contentHtml" />
                  <h6 class="text-right text-sm text-muted-foreground">Submitted {{ idea.createdAtForHumans }}</h6>
                  <div class="flex flex-wrap justify-between gap-3 border-t border-border pt-4">
                    <form v-if="idea.can.deleteApplication" :action="application.routes.destroy" method="POST">
                      <CsrfField />
                      <MethodField method="DELETE" />
                      <Button type="submit" variant="destructive" size="sm">Decline Application</Button>
                    </form>
                    <form v-if="idea.can.updateApplication" :action="application.routes.approve" method="POST">
                      <CsrfField />
                      <MethodField method="PUT" />
                      <Button type="submit" variant="success" size="sm">Approve Application</Button>
                    </form>
                  </div>
                </CardContent>
              </Card>
            </template>
            <p v-else>No pending applications.</p>
            <PaginationLinks :paginator="applications" :only="['applications']" label="Application pages" />
          </div>

          <div v-else key="collaborators" class="flex flex-col gap-3">
            <template v-if="collaborators.items.length > 0">
              <div v-for="collaborator in collaborators.items" :key="collaborator.id" class="flex items-center justify-between gap-4 rounded-md border border-border bg-card px-4 py-3">
                <a class="text-primary hover:underline" :href="collaborator.user.routes.show">
                  {{ collaborator.user.firstName }} {{ collaborator.user.lastName }}
                </a>
                <form v-if="idea.can.deleteApplication" :action="collaborator.routes.destroy" method="POST">
                  <CsrfField />
                  <MethodField method="DELETE" />
                  <Button type="submit" variant="destructive" size="sm">Remove Collaborator</Button>
                </form>
              </div>
            </template>
            <p v-else>No collaborators have joined yet.</p>
            <PaginationLinks :paginator="collaborators" :only="['collaborators']" label="Collaborator pages" />
          </div>
        </Transition>
      </div>

      <aside class="flex flex-col gap-3">
        <Card>
          <CardHeader>
            <div class="flex flex-col gap-1">
              <h2 class="text-lg font-semibold text-white">Lifecycle</h2>
              <p class="text-sm text-muted-foreground">{{ idea.statusHelp }}</p>
            </div>
          </CardHeader>
          <CardContent class="flex flex-col gap-3">
            <div class="grid gap-2">
              <form v-for="statusOption in idea.availableStatuses" :key="statusOption.value" :action="idea.routes.statusUpdate" method="POST">
                <CsrfField />
                <MethodField method="PATCH" />
                <input type="hidden" name="status" :value="statusOption.value">
                <Button
                  type="submit"
                  :variant="idea.status === statusOption.value ? 'secondary' : 'outline'"
                  size="sm"
                  class="w-full justify-start"
                  :disabled="idea.status === statusOption.value"
                >
                  <RefreshCw class="size-4" aria-hidden="true" />
                  {{ statusOption.label }}
                </Button>
                <p class="mt-1 text-xs text-muted-foreground">{{ statusOption.description }}</p>
              </form>
            </div>
          </CardContent>
        </Card>

        <IdeaSidebar :idea="idea" />
      </aside>
    </div>
  </section>
</template>
