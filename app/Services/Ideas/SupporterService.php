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
 */
class SupporterService
{
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
     * @throws Exception
     */
    public function destroy(IdeaSupporter $supporter): bool
    {
        return $supporter->delete();
    }

    /**
     * @return Model|HasMany|object|null
     */
    public function getSupportFromUser(Idea $idea)
    {
        return $idea->hasSupportFromUser(Auth::user()->id);
    }
}
