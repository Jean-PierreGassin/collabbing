<?php

namespace App\Services\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Repositories\Ideas\ApplicationRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Class ApplicationService
 */
class ApplicationService
{
    public function __construct(private ApplicationRepository $applications) {}

    public function create(Idea $idea, array $data): bool
    {
        $application = $this->applications->create($idea, Auth::user(), $data);

        return $application->exists;
    }

    public function approve(IdeaApplication $application): bool
    {
        return $this->applications->approve($application);
    }

    /**
     * @throws \Exception
     */
    public function destroy(IdeaApplication $application): bool
    {
        return $this->applications->destroy($application);
    }

    public function getPendingApplications(Idea $idea): Collection
    {
        return $this->applications->getPendingApplications($idea);
    }

    public function getApprovedApplications(Idea $idea): Collection
    {
        return $this->applications->getApprovedApplications($idea);
    }

    /**
     * @return Model|null
     */
    public function getApplicationFromUser(Idea $idea, string $type)
    {
        return $this->applications->getApplicationFromUser($idea, Auth::user(), $type);
    }
}
