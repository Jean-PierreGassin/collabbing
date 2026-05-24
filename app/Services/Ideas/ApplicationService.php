<?php

namespace App\Services\Ideas;

use App\Data\Ideas\IdeaApplicationData;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use App\Notifications\Ideas\NewIdeaApplicationNotification;
use App\Repositories\Ideas\ApplicationRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ApplicationService
{
    public function __construct(private ApplicationRepository $applications) {}

    public function create(Idea $idea, IdeaApplicationData $data): bool
    {
        $application = $this->applications->create($idea, $this->authenticatedUser(), $data);
        $owner = $idea->owner();

        if ($owner instanceof User) {
            $owner->notify(new NewIdeaApplicationNotification($idea, $application));
        }

        return $application->exists;
    }

    public function update(IdeaApplication $application, IdeaApplicationData $data): bool
    {
        return $this->applications->update($application, $data);
    }

    public function approve(IdeaApplication $application): bool
    {
        return $this->applications->approve($application);
    }

    public function destroy(IdeaApplication $application): bool
    {
        return $this->applications->destroy($application);
    }

    public function withdraw(IdeaApplication $application): bool
    {
        return $this->applications->withdraw($application);
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
