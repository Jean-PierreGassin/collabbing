<?php

namespace App\Data\Ideas;

final readonly class IdeaData
{
    public function __construct(
        public string $title,
        public string $summary,
        public string $repositoryName,
        public string $communication,
        public string $content,
        public string $status
    ) {}

    public function ideaAttributes(): array
    {
        return [
            'title' => $this->title,
            'summary' => $this->summary,
            'communication' => $this->communication,
            'content' => $this->content,
            'status' => $this->status,
        ];
    }
}
