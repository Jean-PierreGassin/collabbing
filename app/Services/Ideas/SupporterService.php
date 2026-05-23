<?php

namespace App\Services\Ideas;

use App\Models\Idea;
use App\Models\IdeaSupporter;
use App\Models\User;
use App\Repositories\Ideas\SupporterRepository;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class SupporterService
{
    public function __construct(private SupporterRepository $supporters) {}

    public function create(Idea $idea): IdeaSupporter
    {
        return $this->supporters->create($idea, $this->authenticatedUser());
    }

    public function destroy(IdeaSupporter $supporter): bool
    {
        return $this->supporters->destroy($supporter);
    }

    public function getSupportFromUser(Idea $idea): ?IdeaSupporter
    {
        return $this->supporters->getSupportFromUser($idea, $this->authenticatedUser());
    }

    private function authenticatedUser(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            throw new RuntimeException('An authenticated user is required.');
        }

        return $user;
    }
}
