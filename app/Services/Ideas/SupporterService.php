<?php

namespace App\Services\Ideas;

use App\Models\Idea;
use App\Models\IdeaSupporter;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * Class SupporterService
 * @package App\Services\Ideas
 */
class SupporterService
{
    /**
     * @param Idea $idea
     * @return Model
     */
    public function create(Idea $idea): Model
    {
        return $idea->supporters()->firstOrCreate(
            [
                'user_id' => Auth::user()->id,
                'idea_id' => $idea->id,
            ]
        );
    }

    /**
     * @param IdeaSupporter $supporter
     * @return bool
     * @throws Exception
     */
    public function destroy(IdeaSupporter $supporter): bool
    {
        return $supporter->delete();
    }

    /**
     * @param Idea $idea
     * @return Model|HasMany|object|null
     */
    public function getSupportFromUser(Idea $idea)
    {
        return $idea->hasSupportFromUser(Auth::user()->id);
    }
}