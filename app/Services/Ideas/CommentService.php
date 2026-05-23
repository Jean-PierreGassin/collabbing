<?php

namespace App\Services\Ideas;

use App\Data\Ideas\IdeaCommentData;
use App\Models\Idea;
use App\Models\IdeaComment;
use App\Models\User;
use App\Repositories\Ideas\CommentRepository;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class CommentService
{
    public function __construct(private CommentRepository $comments) {}

    public function store(Idea $idea, IdeaCommentData $data): IdeaComment
    {
        return $this->comments->create($idea, $this->authenticatedUser(), $data);
    }

    public function update(IdeaComment $comment, IdeaCommentData $data): bool
    {
        return $this->comments->update($comment, $data);
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
