<?php

namespace App\Services\Ideas;

use App\Models\Idea;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Class IdeaService
 */
class IdeaService
{
    public function create(array $data): Idea
    {
        return Auth::user()->ideas()->create($data);
    }

    public function update(Idea $idea, array $data): bool
    {
        $idea->update($data);

        return $idea->save();
    }

    public function getTrending(): Collection
    {
        return Idea::where('status', 'open')
            ->withCount('supporters')
            ->orderBy('supporters_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->limit(3)
            ->get();
    }

    public function search(string $search): LengthAwarePaginator
    {
        return Idea::where('status', 'open')
            ->orderBy('created_at', 'desc')
            ->where('title', 'like', "{$search}%")
            ->paginate(10);
    }

    public function getUserIdeas(): LengthAwarePaginator
    {
        return Auth::user()->ideas()
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'ideas');
    }

    public function getCollaboratedIdeas(): LengthAwarePaginator
    {
        $collaborationIds = Auth::user()->collaborations()->pluck('idea_id');

        return Idea::whereIn('id', $collaborationIds)
            ->paginate(5, ['*'], 'collaborations');
    }
}
