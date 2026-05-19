<?php

namespace App\Repositories\Ideas;

use App\Models\Idea;
use App\Models\RepositoryEvent;
use Illuminate\Pagination\LengthAwarePaginator;

class RepositoryEventRepository
{
    public function getForIdea(Idea $idea): LengthAwarePaginator
    {
        $idea->loadMissing('codeRepository');

        $codeRepository = $idea->latestCodeRepository();

        if (! $codeRepository) {
            return RepositoryEvent::query()
                ->whereRaw('1 = 0')
                ->paginate(10, ['*'], 'repository_events');
        }

        return RepositoryEvent::query()
            ->where('code_repository_id', $codeRepository->id)
            ->latest('occurred_at')
            ->paginate(10, ['*'], 'repository_events');
    }
}
