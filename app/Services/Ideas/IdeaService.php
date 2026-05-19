<?php

namespace App\Services\Ideas;

use App\Data\Ideas\IdeaData;
use App\Data\Ideas\IdeaStatusData;
use App\Models\Idea;
use App\Models\User;
use App\Repositories\Ideas\IdeaRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class IdeaService
{
    public function __construct(private IdeaRepository $ideas) {}

    public function create(IdeaData $data): Idea
    {
        return $this->ideas->createForUser($this->authenticatedUser(), $data);
    }

    public function update(Idea $idea, IdeaData $data): bool
    {
        return $this->ideas->update($idea, $data);
    }

    public function updateStatus(Idea $idea, IdeaStatusData $data): bool
    {
        return $this->ideas->updateStatus($idea, $data->status);
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

    public function getUserIdeas(?string $search = null): LengthAwarePaginator
    {
        return $this->ideas->getUserIdeas($this->authenticatedUser(), $search);
    }

    public function getCollaboratedIdeas(?string $search = null): LengthAwarePaginator
    {
        return $this->ideas->getCollaboratedIdeas($this->authenticatedUser(), $search);
    }

    public function getComments(Idea $idea): LengthAwarePaginator
    {
        return $this->ideas->getComments($idea);
    }

    private function authenticatedUser(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            throw new RuntimeException('An authenticated user is required.');
        }

        return $user;
    }
}
