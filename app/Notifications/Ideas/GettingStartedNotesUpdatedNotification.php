<?php

namespace App\Notifications\Ideas;

use App\Models\Idea;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GettingStartedNotesUpdatedNotification extends Notification
{
    public function __construct(public readonly Idea $idea) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Start notes updated for {$this->idea->title}")
            ->greeting('Start notes updated')
            ->line("The private getting-started notes for {$this->idea->title} were updated.")
            ->action('Open idea', route('ideas.show', $this->idea));
    }
}
