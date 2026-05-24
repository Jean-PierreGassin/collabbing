<?php

namespace App\Repositories\Ideas;

use App\Data\Ideas\IdeaData;
use App\Models\CodeRepository;
use App\Models\Idea;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class IdeaRepository
{
    private const INDEX_RELATIONS = [
        'user',
        'codeRepository.events',
    ];

    private const INDEX_COUNTS = [
        'supporters',
        'approvedApplications',
        'pendingApplications',
    ];

    public function createForUser(User $user, IdeaData $data): Idea
    {
        $idea = new Idea($data->ideaAttributes());
        $idea->user()->associate($user);
        $idea->save();

        $codeRepository = new CodeRepository([
            'provider' => CodeRepository::PROVIDER_GITHUB,
            'status' => CodeRepository::STATUS_PLANNED,
            'owner' => $user->githubUsername(),
            'name' => $data->repositoryName,
        ]);

        $idea->codeRepositories()->save($codeRepository);

        $idea->load('codeRepository');

        return $idea;
    }

    public function update(Idea $idea, IdeaData $data): bool
    {
        $idea->update($data->ideaAttributes());

        $idea->loadMissing('user');

        $owner = $idea->owner();

        $idea->codeRepository()->updateOrCreate(
            ['provider' => CodeRepository::PROVIDER_GITHUB],
            [
                'owner' => $owner?->githubUsername(),
                'name' => $data->repositoryName,
            ]
        );

        return $idea->save();
    }

    public function getTrending(): Collection
    {
        return Idea::where('status', 'open')
            ->with(self::INDEX_RELATIONS)
            ->withCount(self::INDEX_COUNTS)
            ->where(function ($query): void {
                $query->has('supporters')
                    ->orWhereHas('approvedApplications');
            })
            ->orderBy('supporters_count', 'desc')
            ->orderBy('approved_applications_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->limit(3)
            ->get();
    }

    public function search(string $search): LengthAwarePaginator
    {
        return $this->browseOpen($search);
    }

    public function browseOpen(?string $search = null, ?string $tag = null): LengthAwarePaginator
    {
        return $this->openIndexQuery()
            ->when($search, fn (Builder $query, string $search): Builder => $this->applyIdeaSearch($query, $search))
            ->when($tag, fn (Builder $query, string $tag): Builder => $this->applyIdeaTag($query, $tag))
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getOpenRecent(): LengthAwarePaginator
    {
        return $this->openIndexQuery()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getPopularTags(int $limit = 12): Collection
    {
        $counts = [];

        Idea::where('status', 'open')
            ->whereNotNull('tags')
            ->select('tags')
            ->orderBy('id')
            ->cursor()
            ->each(function (Idea $idea) use (&$counts): void {
                foreach ($this->normalizedTags($idea->tags) as $tag) {
                    $counts[$tag] = ($counts[$tag] ?? 0) + 1;
                }
            });

        return collect($counts)
            ->map(fn (int $count, string $tag): array => [
                'name' => $tag,
                'count' => $count,
            ])
            ->sort(function (array $first, array $second): int {
                if ($first['count'] === $second['count']) {
                    return $first['name'] <=> $second['name'];
                }

                return $second['count'] <=> $first['count'];
            })
            ->take($limit)
            ->values();
    }

    public function getUserIdeas(User $user, ?string $search = null): LengthAwarePaginator
    {
        return $user->ideas()
            ->with(self::INDEX_RELATIONS)
            ->withCount(self::INDEX_COUNTS)
            ->when($search, fn (Builder $query, string $search): Builder => $this->applyIdeaSearch($query, $search))
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'ideas');
    }

    public function getCollaboratedIdeas(User $user, ?string $search = null): LengthAwarePaginator
    {
        $collaborationIds = $user->collaborations()->pluck('idea_id');

        return Idea::whereIn('id', $collaborationIds)
            ->with(self::INDEX_RELATIONS)
            ->withCount(self::INDEX_COUNTS)
            ->when($search, fn (Builder $query, string $search): Builder => $this->applyIdeaSearch($query, $search))
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'collaborations');
    }

    public function getComments(Idea $idea): LengthAwarePaginator
    {
        return $idea->comments()
            ->whereNull('parent_id')
            ->with([
                'user',
                'replies.user',
            ])
            ->latest()
            ->paginate(5, ['*'], 'comments');
    }

    public function markRepositoryCreated(Idea $idea): bool
    {
        $idea->latestCodeRepository()?->forceFill([
            'status' => CodeRepository::STATUS_ACTIVE,
            'missing_at' => null,
        ])->save();

        return true;
    }

    public function getApprovedApplications(Idea $idea): Collection
    {
        return $idea->approvedApplications()->get();
    }

    private function applyIdeaSearch(Builder $query, string $search): Builder
    {
        $search = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search);

        return $query->where(function (Builder $query) use ($search): void {
            $query->where('title', 'like', "{$search}%")
                ->orWhere('tagline', 'like', "%{$search}%")
                ->orWhere('summary', 'like', "%{$search}%")
                ->orWhere('tags', 'like', "%{$search}%");
        });
    }

    private function applyIdeaTag(Builder $query, string $tag): Builder
    {
        return $query->whereJsonContains('tags', $tag);
    }

    private function openIndexQuery(): Builder
    {
        return Idea::where('status', 'open')
            ->with(self::INDEX_RELATIONS)
            ->withCount(self::INDEX_COUNTS);
    }

    private function normalizedTags(mixed $tags): array
    {
        if (! is_array($tags)) {
            return [];
        }

        return collect($tags)
            ->filter(fn (mixed $tag): bool => is_string($tag) && trim($tag) !== '')
            ->map(fn (string $tag): string => Str::of($tag)->squish()->lower()->toString())
            ->unique()
            ->values()
            ->all();
    }
}
