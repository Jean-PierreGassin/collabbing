<?php


namespace App\Services\Ideas;


use App\Models\Idea;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Class IdeaService
 * @package App\Services\Ideas
 */
class IdeaService
{
    /**
     * @param array $data
     * @return Idea
     */
    public function create(array $data): Idea
    {
        return Auth::user()->ideas()->create($data);
    }

    /**
     * @param Idea $idea
     * @param array $data
     * @return bool
     */
    public function update(Idea $idea, array $data): bool
    {
        $idea->update($data);
        return $idea->save();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getTrending(): LengthAwarePaginator
    {
        return Idea::where('status', 'open')
            ->withCount('supporters')
            ->orderBy('supporters_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->paginate();
    }

    /**
     * @param string $search
     * @return LengthAwarePaginator
     */
    public function search(string $search): LengthAwarePaginator
    {
        return Idea::where('status', 'open')
            ->orderBy('created_at', 'desc')
            ->where('title', 'like', "%{$search}%")
            ->paginate();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getUserIdeas(): LengthAwarePaginator
    {
        return Auth::user()->ideas()
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'ideas');
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getCollaboratedIdeas(): LengthAwarePaginator
    {
        $collaborationIds = Auth::user()->collaborations()->pluck('idea_id');
        return Idea::whereIn('id', $collaborationIds)
            ->paginate(5, ['*'], 'collaborations');
    }
}