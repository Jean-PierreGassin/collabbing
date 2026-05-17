<?php

namespace App\Services\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Repositories\Ideas\ApplicationRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
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

    public function getPendingApplications(Idea $idea): LengthAwarePaginator
    {
        return $this->applications->getPendingApplications($idea);
    }

    public function getApprovedApplications(Idea $idea): LengthAwarePaginator
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
