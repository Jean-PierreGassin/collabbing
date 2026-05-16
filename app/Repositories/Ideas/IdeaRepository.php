<?php

namespace App\Repositories\Ideas;

use App\Models\Idea;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class IdeaRepository
{
    public function createForUser(User $user, array $data): Idea
    {
        return $user->ideas()->create($data);
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

    public function getOpenRecent(): LengthAwarePaginator
    {
        return Idea::where('status', 'open')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getUserIdeas(User $user): LengthAwarePaginator
    {
        return $user->ideas()
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'ideas');
    }

    public function getCollaboratedIdeas(User $user): LengthAwarePaginator
    {
        $collaborationIds = $user->collaborations()->pluck('idea_id');

        return Idea::whereIn('id', $collaborationIds)
            ->paginate(5, ['*'], 'collaborations');
    }

    public function getComments(Idea $idea): LengthAwarePaginator
    {
        return $idea->comments()->with('user')->paginate(10);
    }

    public function markRepositoryCreated(Idea $idea): bool
    {
        return $this->update($idea, [
            'repository' => true,
            'repository_missing_at' => null,
        ]);
    }

    public function getApprovedApplications(Idea $idea): Collection
    {
        return $idea->approvedApplications()->get();
    }
}
