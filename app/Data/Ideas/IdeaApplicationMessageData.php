<?php

namespace App\Data\Ideas;

final readonly class IdeaApplicationMessageData
{
    public function __construct(
        public string $body
    ) {}
}
