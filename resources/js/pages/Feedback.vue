<script setup lang="ts">
import { Mail } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
</script>

<template>
  <section class="flex max-w-3xl flex-col gap-5">
    <div class="flex flex-col gap-1">
      <h1 class="text-2xl font-semibold text-white">
        Submit feedback
      </h1>
      <p class="text-sm leading-6 text-muted-foreground">
        Share bugs, product suggestions, or workflow friction so the workspace can keep improving.
      </p>
    </div>

    <Card>
      <CardHeader>
        <div class="flex items-center gap-3">
          <span class="flex size-10 items-center justify-center rounded-md bg-primary/10 text-primary">
            <Mail
              class="size-5"
              aria-hidden="true" />
          </span>
          <CardTitle>
            Email feedback
          </CardTitle>
        </div>
      </CardHeader>
      <CardContent class="flex flex-col gap-4">
        <p class="text-sm leading-6 text-muted-foreground">
          Include the page, what you expected, and what happened. Screenshots or short reproduction steps help.
        </p>
        <Button
          as="a"
          href="mailto:jeanpierre.gassin@gmail.com?subject=Collabbing Feedback"
          class="w-fit">
          Email feedback
        </Button>
      </CardContent>
    </Card>
  </section>
</template>
