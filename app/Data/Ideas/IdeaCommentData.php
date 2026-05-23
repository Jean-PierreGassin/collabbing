<?php

namespace App\Data\Ideas;

final readonly class IdeaCommentData
{
    public function __construct(
        public string $content,
        public ?int $parentId = null
    ) {}

    public function createAttributes(): array
    {
        return [
            'content' => $this->content,
            'parent_id' => $this->parentId,
        ];
    }

    public function updateAttributes(): array
    {
        return [
            'content' => $this->content,
        ];
    }
}
