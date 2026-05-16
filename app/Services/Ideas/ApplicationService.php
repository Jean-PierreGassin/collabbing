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
 */
class ApplicationService
{
    public function create(Idea $idea, array $data): bool
    {
        $data['user_id'] = Auth::user()->id;
        $application = $idea->applications()->create($data);

        return $application->save();
    }

    public function approve(IdeaApplication $application): bool
    {
        $application->status = 'approved';

        return $application->save();
    }

    /**
     * @throws \Exception
     */
    public function destroy(IdeaApplication $application): bool
    {
        return $application->delete();
    }

    public function getPendingApplications(Idea $idea): Collection
    {
        return $idea->pendingApplications()->get();
    }

    public function getApprovedApplications(Idea $idea): Collection
    {
        return $idea->approvedApplications()->get();
    }

    /**
     * @return Model|HasMany|object|null
     */
    public function getApplicationFromUser(Idea $idea, string $type)
    {
        return $idea->hasApplicationFromUser(Auth::user()->id, $type);
    }
}
