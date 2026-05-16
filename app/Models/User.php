<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 *
 * @property string $username
 * @property string $first_name
 * @property string $last_name
 * @property string $github_token
 * @property string $github_username
 */
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

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

    protected function casts(): array
    {
        return [
            'github_token' => 'encrypted',
        ];
    }

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

    public function getNameAttribute()
    {
        return "$this->first_name $this->last_name";
    }

    public function profilePicture(): string
    {
        if ($this->github_username) {
            return 'https://github.com/'.rawurlencode($this->github_username).'.png?size=200';
        }

        return self::DEFAULT_PROFILE_PICTURE;
    }

    public function hasGithubToken(): bool
    {
        $token = $this->getRawOriginal('github_token');

        return is_string($token) && $token !== '';
    }
}
