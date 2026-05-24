<?php

namespace App\Repositories\Ideas;

use App\Data\Ideas\IdeaApplicationData;
use App\Data\Ideas\IdeaApplicationDecisionData;
use App\Data\Ideas\IdeaApplicationMessageData;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaApplicationMessage;
use App\Models\IdeaApplicationReadState;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use RuntimeException;

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

    public function approve(IdeaApplication $application, ?IdeaApplicationDecisionData $data = null): bool
    {
        if (! $application->isPending()) {
            return false;
        }

        $application->forceFill([
            'approval_note' => $data?->approvalNote,
            'status' => IdeaApplication::STATUS_APPROVED,
        ]);

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

    public function destroy(IdeaApplication $application, ?IdeaApplicationDecisionData $data = null): bool
    {
        if ($application->isApproved()) {
            $application->forceFill([
                'status' => IdeaApplication::STATUS_REMOVED,
                'removed_at' => Carbon::now('UTC'),
            ]);
            $messageType = IdeaApplicationMessage::TYPE_REMOVED;
            $messageBody = $data?->exitReason;
        } elseif ($application->isPending()) {
            $application->forceFill([
                'decline_reason' => $data?->declineReason,
                'status' => IdeaApplication::STATUS_DECLINED,
            ]);
            $messageType = IdeaApplicationMessage::TYPE_DECLINED;
            $messageBody = null;
        } else {
            return false;
        }

        if (! $application->save()) {
            return false;
        }

        $this->recordSystemMessage($application, $messageType, $messageBody);

        return true;
    }

    public function leave(IdeaApplication $application, ?IdeaApplicationDecisionData $data = null): bool
    {
        if (! $application->isApproved()) {
            return false;
        }

        $application->forceFill([
            'status' => IdeaApplication::STATUS_LEFT,
            'left_at' => Carbon::now('UTC'),
        ]);

        if (! $application->save()) {
            return false;
        }

        $this->recordSystemMessage($application, IdeaApplicationMessage::TYPE_LEFT, $data?->exitReason);

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

    public function createUserMessage(IdeaApplication $application, User $user, IdeaApplicationMessageData $data): IdeaApplicationMessage
    {
        $message = $application->messages()->create([
            'user_id' => $user->id,
            'type' => IdeaApplicationMessage::TYPE_MESSAGE,
            'body' => $data->body,
            'occurred_at' => Carbon::now('UTC'),
        ]);

        if (! $message instanceof IdeaApplicationMessage) {
            throw new RuntimeException('Application message could not be created.');
        }

        return $message;
    }

    public function messagesFor(IdeaApplication $application): Collection
    {
        return $application->messages()
            ->with('user')
            ->get();
    }

    public function unreadMessagesCount(IdeaApplication $application, User $user): int
    {
        $readState = $application->readStates()
            ->where('user_id', $user->id)
            ->first();

        $query = $application->messages()
            ->where(function ($query) use ($user): void {
                $query->whereNull('user_id')
                    ->orWhere('user_id', '!=', $user->id);
            });

        if ($readState instanceof IdeaApplicationReadState && $readState->last_read_at) {
            $query->where('occurred_at', '>', $readState->last_read_at);
        }

        return $query->count();
    }

    public function markThreadRead(IdeaApplication $application, User $user): IdeaApplicationReadState
    {
        return IdeaApplicationReadState::query()->updateOrCreate(
            [
                'idea_application_id' => $application->id,
                'user_id' => $user->id,
            ],
            [
                'last_read_at' => Carbon::now('UTC'),
            ]
        );
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

    public function getLatestFinalApplicationFromUser(Idea $idea, User $user): ?IdeaApplication
    {
        $application = $idea->applications()
            ->where('user_id', $user->id)
            ->whereNotIn('status', IdeaApplication::activeStatuses())
            ->latest()
            ->first();

        if (! $application instanceof IdeaApplication) {
            return null;
        }

        return $application;
    }

    private function recordSystemMessage(IdeaApplication $application, string $type, ?string $body = null): void
    {
        $application->messages()->create([
            'type' => $type,
            'body' => $body,
            'occurred_at' => Carbon::now('UTC'),
        ]);
    }
}
