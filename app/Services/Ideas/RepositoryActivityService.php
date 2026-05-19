<?php

namespace App\Services\Ideas;

use App\Models\Idea;
use App\Repositories\Ideas\RepositoryEventRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class RepositoryActivityService
{
    public function __construct(private RepositoryEventRepository $events) {}

    public function getForIdea(Idea $idea): LengthAwarePaginator
    {
        return $this->events->getForIdea($idea);
    }
}
