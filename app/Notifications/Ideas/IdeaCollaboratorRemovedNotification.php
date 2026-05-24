<?php

namespace App\Notifications\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IdeaCollaboratorRemovedNotification extends Notification
{
    public function __construct(
        public readonly Idea $idea,
        public readonly IdeaApplication $application,
        public readonly ?string $reason = null,
        public readonly bool $shouldReviewRepositoryAccess = false
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("Collaboration update for {$this->idea->title}")
            ->greeting('Collaboration update')
            ->line("You have been removed from the collaboration on {$this->idea->title}.");

        if ($this->reason) {
            $message->line("Reason: {$this->reason}");
        }

        return $message->action('Open idea', route('ideas.show', $this->idea));
    }
}
