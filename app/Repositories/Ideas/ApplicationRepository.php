<?php

namespace App\Repositories\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ApplicationRepository
{
    public function create(Idea $idea, User $user, array $data): Model
    {
        $data['user_id'] = $user->id;

        return $idea->applications()->create($data);
    }

    public function approve(IdeaApplication $application): bool
    {
        $application->status = 'approved';

        return $application->save();
    }

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

    public function getApplicationFromUser(Idea $idea, User $user, string $type): ?Model
    {
        return $idea->applications()
            ->where('user_id', $user->id)
            ->where('status', $type)
            ->first();
    }
}
