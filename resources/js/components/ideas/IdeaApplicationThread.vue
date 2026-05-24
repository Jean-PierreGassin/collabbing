<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { MessageSquare } from '@lucide/vue';
import type { IdeaApplication } from '@/types/domain';

const props = withDefaults(defineProps<{
  application: IdeaApplication;
  title?: string;
}>(), {
  title: 'Private application thread',
});

const thread = computed(() => props.application.thread);
const messages = computed(() => thread.value?.messages ?? []);
const csrfToken = computed(() => document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '');
const localUnreadCount = ref(thread.value?.unreadCount ?? 0);
const hasUnread = computed(() => localUnreadCount.value > 0);

watch(
  () => thread.value?.unreadCount ?? 0,
  (count) => {
    localUnreadCount.value = count;
  },
);

onMounted(() => {
  void markThreadRead();
});

function systemLabel(type: string): string {
  if (type === 'approved') {
    return 'Application approved';
  }

  if (type === 'declined') {
    return 'Application declined';
  }

  if (type === 'withdrawn') {
    return 'Application withdrawn';
  }

  if (type === 'left') {
    return 'Collaborator left';
  }

  if (type === 'removed') {
    return 'Collaborator removed';
  }

  return 'Application update';
}

async function markThreadRead(): Promise<void> {
  const currentThread = thread.value;

  if (!currentThread || localUnreadCount.value === 0) {
    return;
  }

  try {
    const response = await fetch(currentThread.routes.read, {
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrfToken.value,
        'X-Requested-With': 'XMLHttpRequest',
      },
      method: 'PUT',
    });

    if (response.ok) {
      localUnreadCount.value = 0;
    }
  } catch {
    return;
  }
}
</script>

<template>
  <section
    v-if="thread"
    class="flex flex-col gap-3 rounded-md border border-border bg-background/35 p-3"
    aria-label="Private application thread">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
      <div class="flex min-w-0 flex-col gap-1">
        <h3 class="inline-flex items-center gap-2 text-sm font-semibold text-white">
          <MessageSquare
            class="size-4 text-primary"
            aria-hidden="true" />
          {{ title }}
        </h3>
        <p class="text-xs leading-5 text-muted-foreground">
          Visible only to the idea owner and applicant.
        </p>
      </div>
      <div class="flex shrink-0 flex-wrap gap-1.5">
        <Badge
          v-if="hasUnread"
          variant="default">
          {{ localUnreadCount.toLocaleString() }} new
        </Badge>
        <Badge
          v-if="thread.isReadOnly"
          variant="outline">
          Read-only
        </Badge>
      </div>
    </div>

    <div
      v-if="messages.length > 0"
      class="flex flex-col gap-2">
      <article
        v-for="message in messages"
        :key="message.id"
        class="rounded-md border border-border bg-card/80 px-3 py-2">
        <div
          v-if="message.isSystem"
          class="flex flex-col gap-2">
          <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-muted-foreground">
            <span>{{ systemLabel(message.type) }}</span>
            <span v-if="message.occurredAtForHumans">{{ message.occurredAtForHumans }}</span>
          </div>
          <MarkdownContent
            v-if="message.bodyHtml"
            :html="message.bodyHtml" />
        </div>
        <div
          v-else
          class="flex flex-col gap-2">
          <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-muted-foreground">
            <span class="font-medium text-foreground">{{ message.user?.name ?? 'A collaborator' }}</span>
            <span v-if="message.occurredAtForHumans">{{ message.occurredAtForHumans }}</span>
          </div>
          <MarkdownContent
            v-if="message.bodyHtml"
            :html="message.bodyHtml" />
        </div>
      </article>
    </div>

    <p
      v-else
      class="rounded-md border border-dashed border-border px-3 py-2 text-sm text-muted-foreground">
      No private thread messages yet.
    </p>

    <form
      v-if="thread.canMessage"
      :action="thread.routes.store"
      method="POST"
      class="flex flex-col gap-2">
      <CsrfField />
      <textarea
        name="body"
        class="min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-ring/40"
        maxlength="1500"
        placeholder="Ask a question or clarify the next step."
        required
      />
      <div class="flex justify-end">
        <Button
          type="submit"
          size="sm">
          Send message
        </Button>
      </div>
    </form>

    <p
      v-else-if="thread.readOnlyReason"
      class="text-xs leading-5 text-muted-foreground">
      {{ thread.readOnlyReason }}
    </p>
  </section>
</template>
