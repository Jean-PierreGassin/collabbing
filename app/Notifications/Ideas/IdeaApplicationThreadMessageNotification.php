<?php

namespace App\Notifications\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaApplicationMessage;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class IdeaApplicationThreadMessageNotification extends Notification
{
    public function __construct(
        public readonly Idea $idea,
        public readonly IdeaApplication $application,
        public readonly IdeaApplicationMessage $message
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->message->loadMissing('user');

        return (new MailMessage)
            ->subject("New application thread message for {$this->idea->title}")
            ->greeting('New application thread message')
            ->line("{$this->senderName()} added a private application thread message on {$this->idea->title}.")
            ->line($this->messagePreview())
            ->action('Open application thread', $this->threadRoute($notifiable));
    }

    private function senderName(): string
    {
        $user = $this->message->user;

        if (! $user instanceof User) {
            return 'A collaborator';
        }

        return $user->name;
    }

    private function messagePreview(): string
    {
        $body = $this->message->body;

        if (! $body) {
            return 'They added a message to the private application thread.';
        }

        return Str::limit(Str::of($body)->squish()->toString(), 180);
    }

    private function threadRoute(object $notifiable): string
    {
        if ($notifiable instanceof User && (int) $notifiable->id === (int) $this->idea->user_id) {
            return route('ideas.dashboard', $this->idea);
        }

        return route('ideas.show', $this->idea);
    }
}
