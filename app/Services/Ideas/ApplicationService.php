<?php

namespace App\Services\Ideas;

use App\Data\Ideas\IdeaApplicationData;
use App\Data\Ideas\IdeaApplicationDecisionData;
use App\Data\Ideas\IdeaApplicationMessageData;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaApplicationMessage;
use App\Models\User;
use App\Notifications\Ideas\IdeaApplicationApprovedNotification;
use App\Notifications\Ideas\IdeaApplicationDeclinedNotification;
use App\Notifications\Ideas\IdeaApplicationThreadMessageNotification;
use App\Notifications\Ideas\IdeaApplicationWithdrawnNotification;
use App\Notifications\Ideas\IdeaCollaboratorLeftNotification;
use App\Notifications\Ideas\IdeaCollaboratorRemovedNotification;
use App\Notifications\Ideas\NewIdeaApplicationNotification;
use App\Repositories\Ideas\ApplicationRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class ApplicationService
{
    public function __construct(private ApplicationRepository $applications) {}

    public function create(Idea $idea, IdeaApplicationData $data): bool
    {
        $application = $this->applications->create($idea, $this->authenticatedUser(), $data);
        $owner = $idea->owner();

        if ($owner instanceof User) {
            $owner->notify(new NewIdeaApplicationNotification($idea, $application));
        }

        return $application->exists;
    }

    public function update(IdeaApplication $application, IdeaApplicationData $data): bool
    {
        return $this->applications->update($application, $data);
    }

    public function approve(IdeaApplication $application, ?IdeaApplicationDecisionData $data = null): bool
    {
        $approved = $this->applications->approve($application, $data);

        if ($approved) {
            $this->markThreadRead($application);
            $this->notifyApplicantApproved($application);
        }

        return $approved;
    }

    public function destroy(IdeaApplication $application, ?IdeaApplicationDecisionData $data = null): bool
    {
        $wasApproved = $application->isApproved();
        $wasPending = $application->isPending();
        $destroyed = $this->applications->destroy($application, $data);

        if ($destroyed) {
            $this->markThreadRead($application);

            if ($wasPending && ! $wasApproved) {
                $this->notifyApplicantDeclined($application);
            }

            if ($wasApproved) {
                $this->notifyCollaboratorRemoved($application, $data?->exitReason);
            }
        }

        return $destroyed;
    }

    public function leave(IdeaApplication $application, ?IdeaApplicationDecisionData $data = null): bool
    {
        $left = $this->applications->leave($application, $data);

        if ($left) {
            $this->markThreadRead($application);
            $this->notifyOwnerCollaboratorLeft($application, $data?->exitReason);
        }

        return $left;
    }

    public function withdraw(IdeaApplication $application): bool
    {
        $withdrawn = $this->applications->withdraw($application);

        if ($withdrawn) {
            $this->markThreadRead($application);
            $this->notifyOwnerApplicationWithdrawn($application);
        }

        return $withdrawn;
    }

    public function message(IdeaApplication $application, IdeaApplicationMessageData $data): IdeaApplicationMessage
    {
        $user = $this->authenticatedUser();
        $message = $this->applications->createUserMessage($application, $user, $data);

        $this->applications->markThreadRead($application, $user);
        $this->notifyThreadRecipient($application, $message, $user);

        return $message;
    }

    public function markThreadRead(IdeaApplication $application): void
    {
        $this->applications->markThreadRead($application, $this->authenticatedUser());
    }

    public function getPendingApplications(Idea $idea): LengthAwarePaginator
    {
        return $this->applications->getPendingApplications($idea);
    }

    public function getApprovedApplications(Idea $idea): LengthAwarePaginator
    {
        return $this->applications->getApprovedApplications($idea);
    }

    public function getApplicationFromUser(Idea $idea, string $type): ?IdeaApplication
    {
        return $this->applications->getApplicationFromUser($idea, $this->authenticatedUser(), $type);
    }

    public function getLatestFinalApplicationFromUser(Idea $idea): ?IdeaApplication
    {
        return $this->applications->getLatestFinalApplicationFromUser($idea, $this->authenticatedUser());
    }

    private function authenticatedUser(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            throw new RuntimeException('An authenticated user is required.');
        }

        return $user;
    }

    private function notifyThreadRecipient(IdeaApplication $application, IdeaApplicationMessage $message, User $sender): void
    {
        $application->loadMissing(['idea.user', 'user']);

        $idea = $application->idea;

        if (! $idea instanceof Idea) {
            return;
        }

        $recipient = $this->threadRecipient($application, $sender);

        if (! $recipient instanceof User) {
            return;
        }

        $cacheKey = "idea-application-thread-email:{$application->id}:{$recipient->id}:{$sender->id}";

        if (! Cache::add($cacheKey, true, now('UTC')->addMinutes(5))) {
            return;
        }

        $recipient->notify(new IdeaApplicationThreadMessageNotification($idea, $application, $message));
    }

    private function notifyApplicantApproved(IdeaApplication $application): void
    {
        $application->loadMissing(['idea', 'user']);
        $idea = $application->idea;
        $applicant = $application->user;

        if ($idea instanceof Idea && $applicant instanceof User) {
            $applicant->notify(new IdeaApplicationApprovedNotification($idea, $application));
        }
    }

    private function notifyApplicantDeclined(IdeaApplication $application): void
    {
        $application->loadMissing(['idea', 'user']);
        $idea = $application->idea;
        $applicant = $application->user;

        if ($idea instanceof Idea && $applicant instanceof User) {
            $applicant->notify(new IdeaApplicationDeclinedNotification($idea, $application));
        }
    }

    private function notifyOwnerApplicationWithdrawn(IdeaApplication $application): void
    {
        $application->loadMissing(['idea.user', 'user']);
        $idea = $application->idea;
        $owner = $idea instanceof Idea ? $idea->owner() : null;

        if ($idea instanceof Idea && $owner instanceof User) {
            $owner->notify(new IdeaApplicationWithdrawnNotification($idea, $application));
        }
    }

    private function notifyOwnerCollaboratorLeft(IdeaApplication $application, ?string $reason): void
    {
        $application->loadMissing(['idea.user', 'user', 'idea.codeRepository']);
        $idea = $application->idea;
        $owner = $idea instanceof Idea ? $idea->owner() : null;

        if ($idea instanceof Idea && $owner instanceof User) {
            $owner->notify(new IdeaCollaboratorLeftNotification(
                idea: $idea,
                application: $application,
                reason: $reason,
                shouldReviewRepositoryAccess: $this->shouldReviewRepositoryAccess($application)
            ));
        }
    }

    private function notifyCollaboratorRemoved(IdeaApplication $application, ?string $reason): void
    {
        $application->loadMissing(['idea.codeRepository', 'user']);
        $idea = $application->idea;
        $collaborator = $application->user;

        if ($idea instanceof Idea && $collaborator instanceof User) {
            $collaborator->notify(new IdeaCollaboratorRemovedNotification(
                idea: $idea,
                application: $application,
                reason: $reason,
                shouldReviewRepositoryAccess: $this->shouldReviewRepositoryAccess($application)
            ));
        }
    }

    private function shouldReviewRepositoryAccess(IdeaApplication $application): bool
    {
        $application->loadMissing('idea.codeRepository');
        $idea = $application->idea;

        return $idea instanceof Idea && $idea->latestCodeRepository()?->isAvailable() === true;
    }

    private function threadRecipient(IdeaApplication $application, User $sender): ?User
    {
        $idea = $application->idea;

        if (! $idea instanceof Idea) {
            return null;
        }

        if ((int) $sender->id === (int) $idea->user_id) {
            $applicant = $application->user;

            if ($applicant instanceof User) {
                return $applicant;
            }

            return null;
        }

        $owner = $idea->owner();

        if ($owner instanceof User) {
            return $owner;
        }

        return null;
    }
}
