<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IdeaSupporter extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'idea_id',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id');
    }

    public function idea()
    {
        return $this->hasOne(Idea::class, 'id');
    }
}
