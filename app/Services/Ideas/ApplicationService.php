<?php

namespace App\Services\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * Class ApplicationService
 * @package App\Services\Ideas
 */
class ApplicationService
{
    /**
     * @param Idea $idea
     * @param array $data
     * @return bool
     */
    public function create(Idea $idea, array $data): bool
    {
        $data['user_id'] = Auth::user()->id;
        $application = $idea->applications()->create($data);

        return $application->save();
    }

    /**
     * @param IdeaApplication $application
     * @return bool
     */
    public function approve(IdeaApplication $application): bool
    {
        $application->status = 'approved';
        return $application->save();
    }

    /**
     * @param IdeaApplication $application
     * @return bool
     * @throws \Exception
     */
    public function destroy(IdeaApplication $application): bool
    {
        return $application->delete();
    }

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

    /**
     * @param Idea $idea
     * @param string $type
     * @return Model|HasMany|object|null
     */
    public function getApplicationFromUser(Idea $idea, string $type)
    {
        return $idea->hasApplicationFromUser(Auth::user()->id, $type);
    }
}