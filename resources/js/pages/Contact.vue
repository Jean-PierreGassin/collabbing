<script setup lang="ts">
import { Mail } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
</script>

<template>
  <section class="flex max-w-3xl flex-col gap-5">
    <div class="flex flex-col gap-1">
      <h1 class="text-2xl font-semibold text-white">Contact</h1>
      <p class="text-sm leading-6 text-muted-foreground">
        Send a general enquiry about Collabbing, partnerships, or anything that does not fit as product feedback.
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
          <CardTitle>Email enquiry</CardTitle>
        </div>
      </CardHeader>
      <CardContent class="flex flex-col gap-4">
        <p class="text-sm leading-6 text-muted-foreground">
          Include a short summary and the best way to get back to you.
        </p>
        <Button
          as="a"
          href="mailto:jeanpierre.gassin@gmail.com?subject=Collabbing Enquiry"
          class="w-fit">
          Email enquiry
        </Button>
      </CardContent>
    </Card>
  </section>
</template>
