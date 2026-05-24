<?php

namespace App\Services\Ideas;

use App\Data\Ideas\IdeaData;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use App\Notifications\Ideas\GettingStartedNotesUpdatedNotification;
use App\Repositories\Ideas\IdeaRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class IdeaService
{
    public function __construct(private IdeaRepository $ideas) {}

    public function create(IdeaData $data): Idea
    {
        return $this->ideas->createForUser($this->authenticatedUser(), $data);
    }

    public function update(Idea $idea, IdeaData $data): bool
    {
        $shouldNotify = $this->shouldNotifyGettingStartedNotes($idea, $data);
        $updated = $this->ideas->update($idea, $data);

        if ($updated && $shouldNotify) {
            $this->notifyCollaboratorsOfGettingStartedNotes($idea);
        }

        return $updated;
    }

    public function getTrending(): Collection
    {
        return $this->ideas->getTrending();
    }

    public function search(string $search): LengthAwarePaginator
    {
        return $this->ideas->search($search);
    }

    public function browseOpen(?string $search = null, ?string $tag = null): LengthAwarePaginator
    {
        return $this->ideas->browseOpen($search, $tag);
    }

    public function getOpenRecent(): LengthAwarePaginator
    {
        return $this->ideas->getOpenRecent();
    }

    public function getPopularTags(): Collection
    {
        return $this->ideas->getPopularTags();
    }

    public function getUserIdeas(?string $search = null): LengthAwarePaginator
    {
        return $this->ideas->getUserIdeas($this->authenticatedUser(), $search);
    }

    public function getCollaboratedIdeas(?string $search = null): LengthAwarePaginator
    {
        return $this->ideas->getCollaboratedIdeas($this->authenticatedUser(), $search);
    }

    public function getComments(Idea $idea): LengthAwarePaginator
    {
        return $this->ideas->getComments($idea);
    }

    private function authenticatedUser(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            throw new RuntimeException('An authenticated user is required.');
        }

        return $user;
    }

    private function shouldNotifyGettingStartedNotes(Idea $idea, IdeaData $data): bool
    {
        return $data->notifyCollaboratorsOfGettingStartedNotes
            && $idea->getting_started_notes !== $data->gettingStartedNotes;
    }

    private function notifyCollaboratorsOfGettingStartedNotes(Idea $idea): void
    {
        $this->ideas
            ->getApprovedApplications($idea)
            ->each(function (IdeaApplication $application) use ($idea): void {
                $application->loadMissing('user');

                $collaborator = $application->user;

                if ($collaborator instanceof User) {
                    $collaborator->notify(new GettingStartedNotesUpdatedNotification($idea));
                }
            });
    }
}
