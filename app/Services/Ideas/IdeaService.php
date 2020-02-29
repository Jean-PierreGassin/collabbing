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
     * @return LengthAwarePaginator
     */
    public function getIdeas(): LengthAwarePaginator
    {
        return Idea::where('status', 'open')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getTrendingIdeas(): LengthAwarePaginator
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
    public function searchIdeas(string $search): LengthAwarePaginator
    {
        return Idea::where('status', 'open')
            ->orderBy('created_at', 'desc')
            ->where('title', 'like', "%{$search}%")
            ->paginate();
    }
}