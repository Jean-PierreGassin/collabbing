<?php

namespace App\Data\Ideas;

use Carbon\CarbonInterface;

final readonly class IdeaData
{
    public function __construct(
        public string $title,
        public string $tagline,
        public string $summary,
        public array $tags,
        public string $repositoryName,
        public string $communication,
        public string $content,
        public string $status,
        public ?string $collaborationStage = null,
        public array $helpWanted = [],
        public ?string $helpWantedNote = null,
        public ?string $firstContribution = null,
        public bool $applicationsOpen = true,
        public ?string $applicationsClosedNote = null,
        public ?string $communicationStyle = null,
        public ?string $communicationNote = null,
        public ?string $gettingStartedNotes = null,
        public ?CarbonInterface $gettingStartedNotesUpdatedAt = null
    ) {}

    public function ideaAttributes(): array
    {
        return [
            'title' => $this->title,
            'tagline' => $this->tagline,
            'summary' => $this->summary,
            'tags' => $this->tags,
            'communication' => $this->communication,
            'content' => $this->content,
            'status' => $this->status,
            'collaboration_stage' => $this->collaborationStage,
            'help_wanted' => $this->helpWanted,
            'help_wanted_note' => $this->helpWantedNote,
            'first_contribution' => $this->firstContribution,
            'applications_open' => $this->applicationsOpen,
            'applications_closed_note' => $this->applicationsClosedNote,
            'communication_style' => $this->communicationStyle,
            'communication_note' => $this->communicationNote,
            'getting_started_notes' => $this->gettingStartedNotes,
            'getting_started_notes_updated_at' => $this->gettingStartedNotesUpdatedAt,
        ];
    }
}
