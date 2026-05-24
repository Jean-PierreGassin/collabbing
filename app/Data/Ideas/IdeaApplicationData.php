<?php

namespace App\Data\Ideas;

final readonly class IdeaApplicationData
{
    public function __construct(
        public string $content,
        public ?string $contributionType = null,
        public ?string $firstAction = null
    ) {}

    public function attributes(): array
    {
        return [
            'content' => $this->content,
            'contribution_type' => $this->contributionType,
            'first_action' => $this->firstAction,
        ];
    }
}
