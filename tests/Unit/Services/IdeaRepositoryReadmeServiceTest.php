<?php

namespace Tests\Unit\Services;

use App\Models\CodeRepository;
use App\Models\Idea;
use App\Services\Ideas\IdeaRepositoryReadmeService;
use PHPUnit\Framework\TestCase;

class IdeaRepositoryReadmeServiceTest extends TestCase
{
    public function testBuildCreatesUsefulReadmeSections(): void
    {
        $idea = new Idea([
            'title' => 'Design Review Matchmaker',
            'summary' => 'Find the right reviewer for design work.',
            'communication' => 'Discord',
            'content' => "## Problem\n\nDesigners need better review loops.",
        ]);
        $repository = new CodeRepository([
            'name' => 'design-review-matchmaker',
        ]);

        $readme = (new IdeaRepositoryReadmeService)->build($idea, $repository);

        $this->assertStringContainsString('# Design Review Matchmaker', $readme);
        $this->assertStringContainsString('Find the right reviewer for design work.', $readme);
        $this->assertStringContainsString('Preferred communication: Discord.', $readme);
        $this->assertStringContainsString('`design-review-matchmaker`', $readme);
        $this->assertStringContainsString("## Problem\n\nDesigners need better review loops.", $readme);
    }

    public function testBuildFallsBackWhenPitchIsEmpty(): void
    {
        $idea = new Idea([
            'title' => '',
            'summary' => '',
            'communication' => 'Email',
            'content' => '',
        ]);
        $repository = new CodeRepository([
            'name' => 'new-idea',
        ]);

        $readme = (new IdeaRepositoryReadmeService)->build($idea, $repository);

        $this->assertStringContainsString('# Collabbing Idea', $readme);
        $this->assertStringContainsString('The project pitch has not been written yet.', $readme);
    }
}
