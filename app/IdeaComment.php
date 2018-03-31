<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IdeaComment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content',
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
