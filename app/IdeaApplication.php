<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IdeaApplication extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'idea_id', 'content', 'status',
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
