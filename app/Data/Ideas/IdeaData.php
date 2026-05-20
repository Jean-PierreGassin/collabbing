<?php

namespace App\Data\Ideas;

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
        public string $status
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
        ];
    }
}
