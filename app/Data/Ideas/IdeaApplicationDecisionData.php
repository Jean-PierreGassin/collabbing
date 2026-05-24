<?php

namespace App\Data\Ideas;

final readonly class IdeaApplicationDecisionData
{
    public function __construct(
        public ?string $approvalNote = null,
        public ?string $declineReason = null
    ) {}
}
