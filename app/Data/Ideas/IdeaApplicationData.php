<?php

namespace App\Data\Ideas;

final readonly class IdeaApplicationData
{
    public function __construct(
        public string $content
    ) {}

    public function attributes(): array
    {
        return [
            'content' => $this->content,
        ];
    }
}
