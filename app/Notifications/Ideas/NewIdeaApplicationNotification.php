<?php

namespace App\Notifications\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewIdeaApplicationNotification extends Notification
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
        $this->application->loadMissing('user');

        return (new MailMessage)
            ->subject("New application for {$this->idea->title}")
            ->greeting('New collaboration application')
            ->line("{$this->applicantName()} applied to collaborate on {$this->idea->title}.")
            ->line("Contribution type: {$this->contributionTypeLabel()}")
            ->line("First action: {$this->firstActionLabel()}")
            ->action('Review application', route('ideas.dashboard', $this->idea));
    }

    private function applicantName(): string
    {
        $user = $this->application->user;

        if (! $user instanceof User) {
            return 'A collaborator';
        }

        return $user->name;
    }

    private function contributionTypeLabel(): string
    {
        return match ($this->application->contribution_type) {
            Idea::HELP_FRONTEND => 'Frontend',
            Idea::HELP_BACKEND => 'Backend',
            Idea::HELP_DESIGN => 'Design',
            Idea::HELP_PRODUCT => 'Product',
            Idea::HELP_TESTING => 'Testing',
            Idea::HELP_DEVOPS => 'DevOps',
            Idea::HELP_WRITING => 'Writing',
            Idea::HELP_RESEARCH => 'Research',
            Idea::HELP_FEEDBACK => 'Feedback',
            Idea::HELP_MARKETING => 'Marketing',
            Idea::HELP_ANYTHING => 'I can help with anything',
            default => 'Not specified',
        };
    }

    private function firstActionLabel(): string
    {
        $firstAction = $this->application->first_action;

        if (! $firstAction) {
            return 'Not specified';
        }

        return Str::limit($firstAction, 180);
    }
}
