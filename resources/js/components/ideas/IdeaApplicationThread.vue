<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import CsrfField from '@/components/forms/CsrfField.vue';
import MarkdownContent from '@/components/typography/MarkdownContent.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useSessionStore } from '@/stores/session';
import { MessageSquare } from '@lucide/vue';
import type { IdeaApplication, IdeaApplicationMessage } from '@/types/domain';

const props = withDefaults(defineProps<{
  application: IdeaApplication;
  title?: string;
}>(), {
  title: 'Private application thread',
});

const session = useSessionStore();
const thread = computed(() => props.application.thread);
const messages = computed(() => thread.value?.messages ?? []);
const csrfToken = computed(() => document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '');
const localUnreadCount = ref(thread.value?.unreadCount ?? 0);
const highlightedMessageIds = ref<Set<number>>(new Set());
const hasUnread = computed(() => localUnreadCount.value > 0);
const currentUserId = computed(() => session.user?.id ?? null);

watch(
  () => thread.value?.unreadCount ?? 0,
  (count) => {
    localUnreadCount.value = count;
    syncHighlightedMessages();
  },
);

watch(
  messages,
  () => {
    syncHighlightedMessages();
  },
  { immediate: true },
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

function syncHighlightedMessages(): void {
  const unreadCount = thread.value?.unreadCount ?? 0;

  if (unreadCount <= 0) {
    highlightedMessageIds.value = new Set();

    return;
  }

  highlightedMessageIds.value = new Set(
    messages.value
      .slice(-unreadCount)
      .map((message) => message.id),
  );
}

function isOwnMessage(message: IdeaApplicationMessage): boolean {
  return !message.isSystem
    && currentUserId.value !== null
    && message.user?.id === currentUserId.value;
}

function isHighlighted(message: IdeaApplicationMessage): boolean {
  return highlightedMessageIds.value.has(message.id);
}

function messageFrameClass(message: IdeaApplicationMessage): string {
  if (message.isSystem) {
    return 'mx-auto max-w-[92%]';
  }

  if (isOwnMessage(message)) {
    return 'ml-auto max-w-[min(32rem,92%)]';
  }

  return 'mr-auto max-w-[min(32rem,92%)]';
}

function messageBubbleClass(message: IdeaApplicationMessage): string {
  const classes = ['rounded-md border px-3 py-2'];

  if (message.isSystem) {
    classes.push('border-border bg-background/55 text-muted-foreground');
  } else if (isOwnMessage(message)) {
    classes.push('border-primary/35 bg-primary/12 text-foreground');
  } else {
    classes.push('border-border bg-card/90 text-foreground');
  }

  if (isHighlighted(message)) {
    classes.push('ring-2 ring-primary/30');
  }

  return classes.join(' ');
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
      class="flex flex-col gap-2"
      role="list">
      <article
        v-for="message in messages"
        :key="message.id"
        :class="messageFrameClass(message)"
        role="listitem">
        <div
          :class="messageBubbleClass(message)"
          :data-new-message="isHighlighted(message) ? 'true' : undefined">
          <div
            v-if="message.isSystem"
            class="flex flex-col gap-2">
            <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-muted-foreground">
              <span class="font-medium text-foreground">{{ systemLabel(message.type) }}</span>
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
              <span class="font-medium text-foreground">
                {{ isOwnMessage(message) ? 'You' : (message.user?.name ?? 'A collaborator') }}
              </span>
              <span class="inline-flex items-center gap-1.5">
                <Badge
                  v-if="isHighlighted(message)"
                  variant="default"
                  class="py-0">
                  New
                </Badge>
                <span v-if="message.occurredAtForHumans">{{ message.occurredAtForHumans }}</span>
              </span>
            </div>
            <MarkdownContent
              v-if="message.bodyHtml"
              :html="message.bodyHtml" />
          </div>
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
