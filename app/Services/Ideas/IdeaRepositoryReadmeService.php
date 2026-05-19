<?php

namespace App\Services\Ideas;

use App\Models\CodeRepository;
use App\Models\Idea;
use Illuminate\Support\Str;

class IdeaRepositoryReadmeService
{
    private const CONTENT_LIMIT = 12000;

    public function build(Idea $idea, CodeRepository $codeRepository): string
    {
        $title = $this->heading($idea->title);
        $summary = $this->plainText($idea->summary);
        $communication = $this->plainText($idea->communication);
        $pitch = $this->pitch($idea->content);
        $repositoryName = $this->plainText($codeRepository->name);

        return trim(implode("\n\n", array_filter([
            "# {$title}",
            $summary,
            '## Collaboration',
            "Preferred communication: {$communication}.",
            "This repository was created from the Collabbing idea workspace for `{$repositoryName}`.",
            '## Pitch',
            $pitch,
        ])))."\n";
    }

    private function heading(?string $value): string
    {
        $heading = $this->plainText($value);

        if ($heading === '') {
            return 'Collabbing Idea';
        }

        return str_replace(["\r", "\n", '#'], [' ', ' ', ''], $heading);
    }

    private function pitch(?string $value): string
    {
        $pitch = trim((string) $value);

        if ($pitch === '') {
            return 'The project pitch has not been written yet.';
        }

        return Str::limit($pitch, self::CONTENT_LIMIT, "\n\n...");
    }

    private function plainText(?string $value): string
    {
        return trim((string) preg_replace('/\s+/', ' ', (string) $value));
    }
}
