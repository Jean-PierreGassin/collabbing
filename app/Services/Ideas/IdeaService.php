<?php

namespace App\Services\Ideas;

use App\Models\Idea;
use App\Repositories\Ideas\IdeaRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Class IdeaService
 */
class IdeaService
{
    public function __construct(private IdeaRepository $ideas) {}

    public function create(array $data): Idea
    {
        return $this->ideas->createForUser(Auth::user(), $data);
    }

    public function update(Idea $idea, array $data): bool
    {
        return $this->ideas->update($idea, $data);
    }

    public function getTrending(): Collection
    {
        return $this->ideas->getTrending();
    }

    public function search(string $search): LengthAwarePaginator
    {
        return $this->ideas->search($search);
    }

    public function getOpenRecent(): LengthAwarePaginator
    {
        return $this->ideas->getOpenRecent();
    }

    public function getUserIdeas(): LengthAwarePaginator
    {
        return $this->ideas->getUserIdeas(Auth::user());
    }

    public function getCollaboratedIdeas(): LengthAwarePaginator
    {
        return $this->ideas->getCollaboratedIdeas(Auth::user());
    }

    public function getComments(Idea $idea): LengthAwarePaginator
    {
        return $this->ideas->getComments($idea);
    }
}
