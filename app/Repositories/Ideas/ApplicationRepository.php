<?php

namespace App\Repositories\Ideas;

use App\Data\Ideas\IdeaApplicationData;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ApplicationRepository
{
    public function create(Idea $idea, User $user, IdeaApplicationData $data): IdeaApplication
    {
        return IdeaApplication::query()->create([
            ...$data->attributes(),
            'idea_id' => $idea->id,
            'user_id' => $user->id,
        ]);
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

    public function getApprovedApplicationsForInvite(Idea $idea): Collection
    {
        return $idea->approvedApplications()
            ->with('user.githubAccount')
            ->get();
    }

    public function getApprovedApplicationPreview(Idea $idea, int $limit): Collection
    {
        return $idea->approvedApplications()
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getApplicationFromUser(Idea $idea, User $user, string $type): ?IdeaApplication
    {
        $application = $idea->applications()
            ->where('user_id', $user->id)
            ->where('status', $type)
            ->first();

        if (! $application instanceof IdeaApplication) {
            return null;
        }

        return $application;
    }
}
