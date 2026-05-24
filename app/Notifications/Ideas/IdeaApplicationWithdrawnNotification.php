<?php

namespace App\Notifications\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IdeaApplicationWithdrawnNotification extends Notification
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
        $applicant = $this->application->user;
        $applicantName = 'An applicant';

        if ($applicant instanceof User) {
            $applicantName = $applicant->name;
        }

        return (new MailMessage)
            ->subject("Application withdrawn for {$this->idea->title}")
            ->greeting('Application withdrawn')
            ->line("{$applicantName} withdrew their application to collaborate on {$this->idea->title}.")
            ->action('Open idea dashboard', route('ideas.dashboard', $this->idea));
    }
}
