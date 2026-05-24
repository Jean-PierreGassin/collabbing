<?php

namespace App\Notifications\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IdeaCollaboratorLeftNotification extends Notification
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
        $collaborator = $this->application->user;
        $collaboratorName = 'A collaborator';

        if ($collaborator instanceof User) {
            $collaboratorName = $collaborator->name;
        }

        $message = (new MailMessage)
            ->subject("Collaborator left {$this->idea->title}")
            ->greeting('Collaborator update')
            ->line("{$collaboratorName} left the collaboration on {$this->idea->title}.");

        if ($this->reason) {
            $message->line("Reason: {$this->reason}");
        }

        if ($this->shouldReviewRepositoryAccess) {
            $message->line('The idea has a connected repository. Review repository access when you are ready.');
        }

        return $message->action('Open idea dashboard', route('ideas.dashboard', $this->idea));
    }
}
