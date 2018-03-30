<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Idea extends Model
{
    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function supporters()
    {
        return $this->hasMany(IdeaSupporter::class);
    }

    public function comments()
    {
        return $this->hasMany(IdeaComment::class);
    }

    public function applicants()
    {
        return $this->hasMany(IdeaApplicant::class);
    }
}
