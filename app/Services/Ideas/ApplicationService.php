<?php

namespace App\Services\Ideas;

use App\Models\Idea;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ApplicationService
 * @package App\Services\Ideas
 */
class ApplicationService
{
    /**
     * @param Idea $idea
     * @return Collection
     */
    public function getPendingApplications(Idea $idea): Collection
    {
        return $idea->pendingApplications()->get();
    }

    /**
     * @param Idea $idea
     * @return Collection
     */
    public function getApprovedApplications(Idea $idea): Collection
    {
        return $idea->approvedApplications()->get();
    }
}