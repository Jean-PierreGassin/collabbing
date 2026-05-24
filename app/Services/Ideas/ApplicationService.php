<?php

namespace App\Services\Ideas;

use App\Data\Ideas\IdeaApplicationData;
use App\Data\Ideas\IdeaApplicationMessageData;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaApplicationMessage;
use App\Models\User;
use App\Notifications\Ideas\IdeaApplicationThreadMessageNotification;
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

    public function approve(IdeaApplication $application): bool
    {
        $approved = $this->applications->approve($application);

        if ($approved) {
            $this->markThreadRead($application);
        }

        return $approved;
    }

    public function destroy(IdeaApplication $application): bool
    {
        $destroyed = $this->applications->destroy($application);

        if ($destroyed) {
            $this->markThreadRead($application);
        }

        return $destroyed;
    }

    public function withdraw(IdeaApplication $application): bool
    {
        $withdrawn = $this->applications->withdraw($application);

        if ($withdrawn) {
            $this->markThreadRead($application);
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

        return $owner instanceof User ? $owner : null;
    }
}
