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
        return $this->belongsTo(User::class, 'user_id');
    }

    public function idea()
    {
        return $this->belongsTo(Idea::class, 'idea_id');
    }
}
