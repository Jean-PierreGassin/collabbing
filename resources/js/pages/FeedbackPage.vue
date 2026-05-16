<script setup lang="ts">
import { ArrowLeft, Mail } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useSessionStore } from '@/stores/session';

const session = useSessionStore();

const feedbackEmail = 'jeanpierre.gassin@gmail.com';
const feedbackHref = `mailto:${feedbackEmail}?subject=Collabbing Feedback`;
</script>

<template>
  <section class="mx-auto flex max-w-3xl flex-col gap-6">
    <Button as="a" :href="session.routes.appResources" variant="ghost" class="w-fit">
      <ArrowLeft class="size-4" aria-hidden="true" />
      Resources
    </Button>

    <Card>
      <CardHeader>
        <div class="mb-2 flex size-10 items-center justify-center rounded-md bg-secondary text-secondary-foreground">
          <Mail class="size-5" aria-hidden="true" />
        </div>
        <CardTitle>Submit feedback</CardTitle>
        <CardDescription>
          Share product feedback, migration notes, or anything that would make collaboration smoother.
        </CardDescription>
      </CardHeader>
      <CardContent class="flex flex-col gap-4">
        <p class="text-sm leading-6 text-muted-foreground">
          Feedback is reviewed directly, so include the page, workflow, or idea that prompted it.
        </p>
        <Button as="a" :href="feedbackHref" class="w-fit">
          Email feedback
          <Mail class="size-4" aria-hidden="true" />
        </Button>
      </CardContent>
    </Card>
  </section>
</template>
