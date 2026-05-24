<?php

namespace App\Notifications\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IdeaApplicationApprovedNotification extends Notification
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
        return (new MailMessage)
            ->subject("Application accepted for {$this->idea->title}")
            ->greeting('You are collaborating')
            ->line("Your application to collaborate on {$this->idea->title} was accepted.")
            ->line('Open the idea to see the public first step, communication preference, and any private start guidance available to you.')
            ->action('Open idea', route('ideas.show', $this->idea));
    }
}
