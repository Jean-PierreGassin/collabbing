<?php

namespace App;

use Github\Client;
use Illuminate\Support\Facades\Cache;
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
        'username', 'first_name', 'last_name', 'bio', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The GitHub client used to authenticate users.
     *
     * @var \Github\Client
     */
    protected $githubClient;

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

    public function createGithubClient($token = null): Client
    {
        if (!$this->githubClient) {
            $this->githubClient = new Client();

            $this->githubClient->authenticate($this->github_token ?? $token, 'http_token');
        }

        return $this->githubClient;
    }

    public function profilePicture()
    {
        if ($this->github_token) {
            return Cache::remember('users.profile-picture', 60, function () {
                $gitHub = $this->createGithubClient();

                return $gitHub->currentUser()->show()['avatar_url'];
            });
        }

        return 'https://www.gravatar.com/avatar/' . md5($this->email);
    }
}
