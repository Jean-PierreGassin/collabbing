<script setup lang="ts">
import { computed } from 'vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import MethodField from '@/components/forms/MethodField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import type { Idea, IdeaApplication, IdeaSupporter } from '@/types/domain';

const props = defineProps<{
  idea: Idea;
  collaborator?: IdeaApplication | null;
  applicant?: IdeaApplication | null;
  supporter?: IdeaSupporter | null;
}>();

const supporterSentence = computed(() => {
  if (props.idea.supportersCount === 0) {
    return "Ain't nobody supportin' this here idea yet.";
  }

  return `There ${props.idea.supportersCount > 1 ? 'are' : 'is'} ${props.idea.supportersCount.toLocaleString()} ${props.idea.supportersCount > 1 ? 'people' : 'person'} supporting this idea.`;
});
</script>

<template>
  <div class="flex flex-col gap-3">
    <Card>
      <CardHeader>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>Collaborators</div>
          <div v-if="idea.can.storeApplication">
            <Button v-if="collaborator" as="a" href="#" size="sm">You're a Collaborator 🤟</Button>
            <Button v-else-if="applicant" as="a" href="#" size="sm">You're an Applicant ✅</Button>
            <Button v-else as="a" :href="idea.routes.applicationsCreate" variant="outline" size="sm">Apply to Collaborate 📝</Button>
          </div>
        </div>
      </CardHeader>
      <CardContent>
        <template v-if="idea.collaborators.length === 0">
          It's quiet... too quiet.
        </template>
        <div v-else class="flex flex-wrap gap-2">
          <a v-for="collab in idea.collaborators" :key="collab.id" :href="collab.user.routes.show">
            <img
              class="size-10 border border-secondary object-cover"
              :src="collab.user.profilePicture"
              :alt="`${collab.user.firstName} ${collab.user.lastName}`"
            >
          </a>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardHeader>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>Supporters</div>
          <div v-if="idea.can.storeSupporter">
            <form v-if="supporter" :action="supporter.routes.destroy" method="POST">
              <CsrfField />
              <MethodField method="DELETE" />
              <Button type="submit" size="sm" variant="outline">Un-Support this Idea 👎</Button>
            </form>
            <form v-else :action="idea.routes.supportersStore" method="POST">
              <CsrfField />
              <Button type="submit" size="sm" variant="outline">Support this Idea 👍</Button>
            </form>
          </div>
        </div>
      </CardHeader>
      <CardContent>
        {{ supporterSentence }}
      </CardContent>
    </Card>
  </div>
</template>
