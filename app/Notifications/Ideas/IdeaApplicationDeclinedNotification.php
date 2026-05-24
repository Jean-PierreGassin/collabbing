<?php

namespace App\Notifications\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IdeaApplicationDeclinedNotification extends Notification
{
    public function __construct(
        public readonly Idea $idea,
        public readonly IdeaApplication $application
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("Application update for {$this->idea->title}")
            ->greeting('Application update')
            ->line("Your application to collaborate on {$this->idea->title} was declined.");

        if ($this->application->decline_reason) {
            $message->line("Reason: {$this->application->decline_reason}");
        }

        return $message->action('Open idea', route('ideas.show', $this->idea));
    }
}
