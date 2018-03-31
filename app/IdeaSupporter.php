<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IdeaSupporter extends Model
{
    public function user()
    {
        return $this->hasOne(User::class, 'id');
    }

    public function idea()
    {
        return $this->hasOne(Idea::class, 'id');
    }
}
