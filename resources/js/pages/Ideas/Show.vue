<script setup lang="ts">
import CsrfField from '@/components/forms/CsrfField.vue';
import FormField from '@/components/forms/FormField.vue';
import CommentList from '@/components/comments/CommentList.vue';
import IdeaCard from '@/components/ideas/IdeaCard.vue';
import IdeaSidebar from '@/components/ideas/IdeaSidebar.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import type { Idea, IdeaApplication, IdeaComment, IdeaSupporter, Paginator } from '@/types/domain';

defineProps<{
  idea: Idea;
  comments: Paginator<IdeaComment>;
  collaborator: IdeaApplication | null;
  applicant: IdeaApplication | null;
  supporter: IdeaSupporter | null;
}>();
</script>

<template>
  <section class="flex flex-col gap-5">
    <h1 class="text-2xl font-semibold text-white">{{ idea.titleDisplay }}</h1>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
      <div class="flex flex-col gap-4">
        <IdeaCard :idea="idea" single />
        <template v-if="idea.can.update || collaborator">
          <CommentList :comments="comments" />
          <Card>
            <CardHeader><h2 class="text-lg font-semibold text-white">Share your Comment</h2></CardHeader>
            <CardContent>
              <form :action="idea.routes.commentsStore" method="POST" class="flex flex-col gap-4">
                <CsrfField />
                <FormField id="content" label="Content" help="Keep it specific and constructive.">
                  <template #default="{ invalid, describedBy }">
                    <textarea
                      id="content"
                      name="content"
                      class="min-h-32 rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40"
                      placeholder="Add context, a suggestion, or a useful question."
                      maxlength="1500"
                      :aria-invalid="invalid || undefined"
                      :aria-describedby="describedBy"
                      required
                    />
                  </template>
                </FormField>
                <Button type="submit" class="self-end" size="sm">Share Comment</Button>
              </form>
            </CardContent>
          </Card>
        </template>
      </div>

      <IdeaSidebar :idea="idea" :collaborator="collaborator" :applicant="applicant" :supporter="supporter" />
    </div>
  </section>
</template>
