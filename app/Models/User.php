<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    public const PROVIDER_GITHUB = 'github';

    private const DEFAULT_PROFILE_PICTURE = 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp';

    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'bio',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function ideas(): HasMany
    {
        return $this->hasMany(Idea::class, 'user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(IdeaComment::class, 'user_id');
    }

    public function collaborations(): HasMany
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
        $account = $this->githubAccount;

        return $account instanceof ConnectedAccount ? $account->token : null;
    }

    public function githubUsername(): ?string
    {
        $account = $this->githubAccount;

        return $account instanceof ConnectedAccount ? $account->provider_username : null;
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
