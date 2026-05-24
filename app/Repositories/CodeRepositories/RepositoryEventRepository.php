<?php

namespace App\Repositories\CodeRepositories;

use App\Models\CodeRepository;
use App\Models\RepositoryEvent;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class RepositoryEventRepository
{
    public function recentFor(CodeRepository $codeRepository, int $limit): Collection
    {
        return $codeRepository->events()
            ->latest('occurred_at')
            ->limit($limit)
            ->get();
    }

    public function paginateFor(CodeRepository $codeRepository, int $perPage = 15): LengthAwarePaginator
    {
        return $codeRepository->events()
            ->latest('occurred_at')
            ->paginate($perPage);
    }

    public function record(CodeRepository $codeRepository, string $type, string $summary, string $dedupeKey, Carbon $occurredAt, array $payload = []): RepositoryEvent
    {
        $event = $codeRepository->events()->firstOrCreate(
            ['dedupe_key' => $dedupeKey],
            [
                'type' => $type,
                'summary' => Str::limit($summary, 255, ''),
                'occurred_at' => $occurredAt,
                'payload' => $payload,
            ]
        );

        if (! $event instanceof RepositoryEvent) {
            throw new RuntimeException('Repository event persistence returned an unexpected model.');
        }

        return $event;
    }
}
