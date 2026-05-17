<?php

namespace App\Repositories\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function getPendingApplications(Idea $idea): LengthAwarePaginator
    {
        return $idea->pendingApplications()
            ->with('user')
            ->latest()
            ->paginate(10, ['*'], 'applications');
    }

    public function getApprovedApplications(Idea $idea): LengthAwarePaginator
    {
        return $idea->approvedApplications()
            ->with('user')
            ->latest()
            ->paginate(10, ['*'], 'collaborators');
    }

    public function getApplicationFromUser(Idea $idea, User $user, string $type): ?Model
    {
        return $idea->applications()
            ->where('user_id', $user->id)
            ->where('status', $type)
            ->first();
    }
}
