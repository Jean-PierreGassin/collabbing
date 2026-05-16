<?php

namespace App\Services\Ideas;

use App\Models\Idea;
use App\Models\IdeaSupporter;
use App\Repositories\Ideas\SupporterRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Class SupporterService
 */
class SupporterService
{
    public function __construct(private SupporterRepository $supporters) {}

    public function create(Idea $idea): Model
    {
        return $this->supporters->create($idea, Auth::user());
    }

    /**
     * @throws Exception
     */
    public function destroy(IdeaSupporter $supporter): bool
    {
        return $this->supporters->destroy($supporter);
    }

    /**
     * @return Model|null
     */
    public function getSupportFromUser(Idea $idea)
    {
        return $this->supporters->getSupportFromUser($idea, Auth::user());
    }
}
