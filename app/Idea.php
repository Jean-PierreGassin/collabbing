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

    public function applications()
    {
        return $this->hasMany(IdeaApplication::class);
    }

    public function pendingApplications()
    {
        return $this->applications()->where('status', 'pending');
    }

    public function approvedApplications()
    {
        return $this->applications()->where('status', 'approved');
    }

    public function hasSupportFromUser($userId)
    {
        return $this->supporters()->where('user_id', $userId)->first();
    }
}
