<?php

namespace App\Services\Ideas;

use App\Models\Idea;
use App\Models\IdeaComment;
use App\Repositories\Ideas\CommentRepository;
use Illuminate\Support\Facades\Auth;

/**
 * Class CommentService
 */
class CommentService
{
    public function __construct(private CommentRepository $comments) {}

    public function store(Idea $idea, array $data): IdeaComment
    {
        return $this->comments->create($idea, Auth::user(), $data);
    }

    public function update(IdeaComment $comment, array $data): bool
    {
        return $this->comments->update($comment, $data);
    }
}
