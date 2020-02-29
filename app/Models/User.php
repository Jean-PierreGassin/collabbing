<?php

namespace App\Models;

use Github\Client;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

/**
 * Class User
 * @package App
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'bio',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The GitHub client used to authenticate users.
     *
     * @var Client
     */
    protected $githubClient;

    /**
     * @return HasMany
     */
    public function ideas(): HasMany
    {
        return $this->hasMany(Idea::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(IdeaComment::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function collaborations()
    {
        return $this->applications()->where('status', 'approved');
    }

    /**
     * @return HasMany
     */
    public function applications(): HasMany
    {
        return $this->hasMany(IdeaApplication::class, 'user_id');
    }

    /**
     * @return mixed|string
     */
    public function profilePicture()
    {
        if ($this->github_token) {
            return Cache::remember(
                'users.profile-picture',
                60,
                function () {
                    $gitHub = $this->createGithubClient();

                    return $gitHub->currentUser()->show()['avatar_url'];
                }
            );
        }

        return 'https://www.gravatar.com/avatar/' . md5($this->email);
    }

    /**
     * @param null $token
     * @return Client
     */
    public function createGithubClient($token = null): Client
    {
        if (!$this->githubClient) {
            $this->githubClient = new Client();

            $this->githubClient->authenticate($this->github_token ?? $token, 'http_token');
        }

        return $this->githubClient;
    }
}
