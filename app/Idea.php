<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Idea extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'communication', 'content', 'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supporters()
    {
        return $this->hasMany(IdeaSupporter::class, 'idea_id');
    }

    public function comments()
    {
        return $this->hasMany(IdeaComment::class, 'idea_id');
    }

    public function applications()
    {
        return $this->hasMany(IdeaApplication::class, 'idea_id');
    }

    public function pendingApplications()
    {
        return $this->applications()->where('status', 'pending');
    }

    public function approvedApplications()
    {
        return $this->applications()->where('status', 'approved');
    }

    public function hasApplicationFromUser($userId, $type)
    {
        return $this->applications()
            ->where('user_id', $userId)
            ->where('status', $type)
            ->first();
    }

    public function hasSupportFromUser($userId)
    {
        return $this->supporters()->where('user_id', $userId)->first();
    }
}
