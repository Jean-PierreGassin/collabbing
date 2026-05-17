<?php

namespace App\Repositories\Ideas;

use App\Models\CodeRepository;
use App\Models\Idea;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class IdeaRepository
{
    private const INDEX_RELATIONS = [
        'user',
        'codeRepository.events',
        'supporters',
        'approvedApplications.user',
    ];

    public function createForUser(User $user, array $data): Idea
    {
        $repositoryName = $data['repository_name'] ?? null;
        unset($data['repository_name']);

        $idea = $user->ideas()->create($data);

        if ($repositoryName) {
            $idea->codeRepository()->create([
                'provider' => CodeRepository::PROVIDER_GITHUB,
                'status' => CodeRepository::STATUS_PLANNED,
                'owner' => $user->githubUsername(),
                'name' => $repositoryName,
            ]);
        }

        return $idea->load('codeRepository');
    }

    public function update(Idea $idea, array $data): bool
    {
        $repositoryName = $data['repository_name'] ?? null;
        unset($data['repository_name']);

        $idea->update($data);

        if ($repositoryName) {
            $idea->loadMissing('user');

            $idea->codeRepository()->updateOrCreate(
                ['provider' => CodeRepository::PROVIDER_GITHUB],
                [
                    'owner' => $idea->user->githubUsername(),
                    'name' => $repositoryName,
                ]
            );
        }

        return $idea->save();
    }

    public function getTrending(): Collection
    {
        return Idea::where('status', 'open')
            ->with(self::INDEX_RELATIONS)
            ->withCount('supporters')
            ->orderBy('supporters_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->limit(3)
            ->get();
    }

    public function search(string $search): LengthAwarePaginator
    {
        $search = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search);

        return Idea::where('status', 'open')
            ->with(self::INDEX_RELATIONS)
            ->orderBy('created_at', 'desc')
            ->where('title', 'like', "{$search}%")
            ->paginate(10);
    }

    public function getOpenRecent(): LengthAwarePaginator
    {
        return Idea::where('status', 'open')
            ->with(self::INDEX_RELATIONS)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getUserIdeas(User $user): LengthAwarePaginator
    {
        return $user->ideas()
            ->with(self::INDEX_RELATIONS)
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'ideas');
    }

    public function getCollaboratedIdeas(User $user): LengthAwarePaginator
    {
        $collaborationIds = $user->collaborations()->pluck('idea_id');

        return Idea::whereIn('id', $collaborationIds)
            ->with(self::INDEX_RELATIONS)
            ->paginate(5, ['*'], 'collaborations');
    }

    public function getComments(Idea $idea): LengthAwarePaginator
    {
        return $idea->comments()->with('user')->paginate(10);
    }

    public function markRepositoryCreated(Idea $idea): bool
    {
        $idea->codeRepository?->forceFill([
            'status' => CodeRepository::STATUS_ACTIVE,
            'missing_at' => null,
        ])->save();

        return true;
    }

    public function getApprovedApplications(Idea $idea): Collection
    {
        return $idea->approvedApplications()->get();
    }
}
