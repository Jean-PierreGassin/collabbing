<?php

namespace App\Repositories\Ideas;

use App\Data\Ideas\IdeaApplicationData;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaApplicationMessage;
use App\Models\User;
use Carbon\Carbon;
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
        $application->status = IdeaApplication::STATUS_APPROVED;

        if (! $application->save()) {
            return false;
        }

        $this->recordSystemMessage($application, IdeaApplicationMessage::TYPE_APPROVED);

        return true;
    }

    public function update(IdeaApplication $application, IdeaApplicationData $data): bool
    {
        return $application->forceFill($data->attributes())->save();
    }

    public function destroy(IdeaApplication $application): bool
    {
        if ($application->isApproved()) {
            $application->forceFill([
                'status' => IdeaApplication::STATUS_REMOVED,
                'removed_at' => Carbon::now('UTC'),
            ]);
            $messageType = IdeaApplicationMessage::TYPE_REMOVED;
        } else {
            $application->status = IdeaApplication::STATUS_DECLINED;
            $messageType = IdeaApplicationMessage::TYPE_DECLINED;
        }

        if (! $application->save()) {
            return false;
        }

        $this->recordSystemMessage($application, $messageType);

        return true;
    }

    public function withdraw(IdeaApplication $application): bool
    {
        $application->forceFill([
            'status' => IdeaApplication::STATUS_WITHDRAWN,
            'withdrawn_at' => Carbon::now('UTC'),
        ]);

        if (! $application->save()) {
            return false;
        }

        $this->recordSystemMessage($application, IdeaApplicationMessage::TYPE_WITHDRAWN);

        return true;
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

    private function recordSystemMessage(IdeaApplication $application, string $type): void
    {
        $application->messages()->create([
            'type' => $type,
            'body' => null,
            'occurred_at' => Carbon::now('UTC'),
        ]);
    }
}
