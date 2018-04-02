<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'first_name', 'last_name', 'github', 'bio', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function ideas()
    {
        return $this->hasMany(Idea::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(IdeaComment::class, 'user_id');
    }

    public function applications()
    {
        return $this->hasMany(IdeaApplication::class, 'user_id');
    }

    public function collaborations()
    {
        return $this->applications()->where('status', 'approved');
    }
}
