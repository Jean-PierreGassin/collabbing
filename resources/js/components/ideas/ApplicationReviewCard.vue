<script setup lang="ts">
import { computed, ref } from 'vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import ConfirmingDestructiveForm from '@/components/forms/ConfirmingDestructiveForm.vue';
import MethodField from '@/components/forms/MethodField.vue';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { ChevronDown, ChevronUp } from '@lucide/vue';
import type { IdeaApplication } from '@/types/domain';

const props = defineProps<{
  application: IdeaApplication;
  canDelete: boolean;
  canUpdate: boolean;
}>();

const isExpanded = ref(false);
const contentId = `application-content-${props.application.id}`;

const contentStateClasses = computed(() => {
  if (isExpanded.value) {
    return 'max-h-none';
  }

  return 'max-h-40 overflow-hidden';
});

const applicantDetails = computed(() => {
  const details: string[] = [];

  if (props.application.user.username) {
    details.push(`@${props.application.user.username}`);
  }

  if (props.application.user.githubUsername) {
    details.push(`GitHub: ${props.application.user.githubUsername}`);
  }

  return details;
});
</script>

<template>
  <Card>
    <CardHeader class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
      <div class="flex min-w-0 items-center gap-3">
        <img
          class="size-11 rounded-full border border-border object-cover"
          :src="application.user.profilePicture"
          :alt="`${application.user.name} profile picture`"
        >
        <div class="min-w-0">
          <h2 class="truncate text-base font-semibold text-foreground">
            <a class="text-primary hover:underline" :href="application.user.routes.show">{{ application.user.name }}</a>
          </h2>
          <div v-if="applicantDetails.length > 0" class="flex flex-wrap gap-x-2 text-sm text-muted-foreground">
            <span v-for="detail in applicantDetails" :key="detail">{{ detail }}</span>
          </div>
        </div>
      </div>
      <p class="text-sm text-muted-foreground">Submitted {{ application.createdAtForHumans }}</p>
    </CardHeader>

    <CardContent class="flex flex-col gap-4">
      <div class="relative">
        <div
          :id="contentId"
          :class="[
            'transition-[max-height] duration-200 ease-out',
            contentStateClasses,
          ]"
        >
          <MarkdownContent :html="application.contentHtml" />
        </div>
        <div v-if="!isExpanded" class="pointer-events-none absolute inset-x-0 bottom-0 h-12 bg-gradient-to-t from-card to-transparent" />
      </div>

      <Button
        type="button"
        variant="ghost"
        size="sm"
        class="self-start"
        :aria-expanded="isExpanded"
        :aria-controls="contentId"
        @click="isExpanded = !isExpanded"
      >
        <ChevronUp v-if="isExpanded" class="size-4" aria-hidden="true" />
        <ChevronDown v-else class="size-4" aria-hidden="true" />
        <span v-if="isExpanded">Show less</span>
        <span v-else>Show full application</span>
      </Button>

      <div class="flex flex-wrap justify-between gap-3 border-t border-border pt-4">
        <ConfirmingDestructiveForm
          v-if="canDelete"
          :action="application.routes.destroy"
          button-label="Decline Application"
          confirm-label="Confirm decline"
          message="Declining permanently removes this application."
        />
        <form v-if="canUpdate" :action="application.routes.approve" method="POST">
          <CsrfField />
          <MethodField method="PUT" />
          <Button type="submit" variant="success" size="sm">Approve Application</Button>
        </form>
      </div>
    </CardContent>
  </Card>
</template>
