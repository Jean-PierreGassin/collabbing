<?php

namespace App\Services\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use App\Repositories\Ideas\ApplicationRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ApplicationService
{
    public function __construct(private ApplicationRepository $applications) {}

    public function create(Idea $idea, array $data): bool
    {
        $application = $this->applications->create($idea, $this->authenticatedUser(), $data);

        return $application->exists;
    }

    public function approve(IdeaApplication $application): bool
    {
        return $this->applications->approve($application);
    }

    public function destroy(IdeaApplication $application): bool
    {
        return $this->applications->destroy($application);
    }

    public function getPendingApplications(Idea $idea): LengthAwarePaginator
    {
        return $this->applications->getPendingApplications($idea);
    }

    public function getApprovedApplications(Idea $idea): LengthAwarePaginator
    {
        return $this->applications->getApprovedApplications($idea);
    }

    public function getApplicationFromUser(Idea $idea, string $type): ?IdeaApplication
    {
        return $this->applications->getApplicationFromUser($idea, $this->authenticatedUser(), $type);
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
