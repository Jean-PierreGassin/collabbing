<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IdeaSupporter extends Model
{
    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function idea()
    {
        return $this->hasOne(Idea::class);
    }
}
