<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import PaginationLinks from '@/components/pagination/PaginationLinks.vue';
import type { IdeaComment, Paginator } from '@/types/domain';

defineProps<{
  comments: Paginator<IdeaComment>;
}>();
</script>

<template>
  <section class="flex flex-col gap-3">
    <h4 class="mt-4 text-xl font-semibold">Collaborator Comments</h4>
    <Card v-for="comment in comments.items" :key="comment.id">
      <CardHeader>
        <h6 class="text-sm">
          <a class="text-primary hover:underline" :href="comment.user.routes.show">
            @{{ comment.user.username }}
          </a>
          <span class="text-muted-foreground">
            Posted {{ comment.createdAtForHumans }}
          </span>
        </h6>
      </CardHeader>
      <CardContent class="flex flex-col gap-3">
        <div class="whitespace-pre-line">{{ comment.content }}</div>
        <h6 v-if="comment.wasEdited" class="text-right text-sm text-muted-foreground">Last edited {{ comment.updatedAtForHumans }}</h6>
        <div v-if="comment.can.update" class="border-t border-border pt-3">
          <Button as="a" :href="comment.routes.edit" variant="secondary" size="sm">Edit Comment</Button>
        </div>
      </CardContent>
    </Card>
    <PaginationLinks :paginator="comments" />
  </section>
</template>
