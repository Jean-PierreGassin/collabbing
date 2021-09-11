<?php

namespace App\Models;

use App\Services\ThirdParty\GitHub\GitHubService;
use Github\Client;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

/**
 * Class User
 * @property string $github_token
 * @property string $github_username
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
        'github_token',
        'github_username',
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
     * @return string
     */
    public function profilePicture(): string
    {
        if ($this->github_token) {
            return Cache::remember(
                "users.profile-picture.{$this->github_username}",
                60,
                function () {
                    return GitHubService::createClient($this->github_token)
                        ->currentUser()->show()['avatar_url'];
                }
            );
        }

        return 'https://www.gravatar.com/avatar/' . md5($this->email);
    }
}
