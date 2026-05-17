<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 *
 * @property string $username
 * @property string $first_name
 * @property string $last_name
 */
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    public const PROVIDER_GITHUB = 'github';

    private const DEFAULT_PROFILE_PICTURE = 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp';

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

    public function ideas(): HasMany
    {
        return $this->hasMany(Idea::class, 'user_id');
    }

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

    public function applications(): HasMany
    {
        return $this->hasMany(IdeaApplication::class, 'user_id');
    }

    public function connectedAccounts(): HasMany
    {
        return $this->hasMany(ConnectedAccount::class);
    }

    public function githubAccount(): HasOne
    {
        return $this->hasOne(ConnectedAccount::class)->where('provider', self::PROVIDER_GITHUB);
    }

    public function getNameAttribute(): string
    {
        return "$this->first_name $this->last_name";
    }

    public function profilePicture(): string
    {
        $githubUsername = $this->githubUsername();

        if ($githubUsername) {
            return 'https://github.com/'.rawurlencode($githubUsername).'.png?size=200';
        }

        return self::DEFAULT_PROFILE_PICTURE;
    }

    public function hasGithubToken(): bool
    {
        $token = $this->githubToken();

        return is_string($token) && $token !== '';
    }

    public function githubToken(): ?string
    {
        return $this->githubAccount?->token;
    }

    public function githubUsername(): ?string
    {
        return $this->githubAccount?->provider_username;
    }

    public function getGithubTokenAttribute(): ?string
    {
        return $this->githubToken();
    }

    public function getGithubUsernameAttribute(): ?string
    {
        return $this->githubUsername();
    }
}
