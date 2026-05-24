<?php

namespace App\Services\Ideas;

use App\Models\CodeRepository;
use App\Models\Idea;
use App\Models\RepositoryEvent;
use App\Repositories\CodeRepositories\RepositoryEventRepository;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class RepositoryActivityService
{
    public function __construct(private RepositoryEventRepository $events) {}

    public function archive(Idea $idea): ?array
    {
        $idea->loadMissing('codeRepository');

        $codeRepository = $idea->latestCodeRepository();

        if (! $codeRepository instanceof CodeRepository) {
            return null;
        }

        return [
            'repository' => [
                'name' => $codeRepository->name,
                'htmlUrl' => $codeRepository->html_url,
                'isMissing' => (bool) $codeRepository->missing_at,
                'lastSyncedAtForHumans' => $this->dateForHumans($codeRepository->synced_at),
            ],
            'events' => $this->eventPaginator($this->events->paginateFor($codeRepository)),
        ];
    }

    private function eventPaginator(LengthAwarePaginator $events): array
    {
        return [
            'items' => collect($events->items())
                ->map(fn (RepositoryEvent $event): array => [
                    'id' => $event->id,
                    'type' => $event->type,
                    'summary' => $event->summary,
                    'occurredAtForHumans' => $this->dateForHumans($event->occurred_at),
                ])
                ->values(),
            'currentPage' => $events->currentPage(),
            'lastPage' => $events->lastPage(),
            'previousPageUrl' => $events->previousPageUrl(),
            'nextPageUrl' => $events->nextPageUrl(),
        ];
    }

    private function dateForHumans(mixed $value): ?string
    {
        if (! $value instanceof Carbon) {
            return null;
        }

        return $value->diffForHumans();
    }
}
