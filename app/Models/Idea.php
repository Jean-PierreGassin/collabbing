<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Idea
 */
class Idea extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'communication',
        'content',
        'status',
        'repository',
        'repository_name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(IdeaComment::class, 'idea_id');
    }

    public function pendingApplications(): HasMany
    {
        return $this->applications()->where('status', 'pending');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(IdeaApplication::class, 'idea_id');
    }

    public function approvedApplications(): HasMany
    {
        return $this->applications()->where('status', 'approved');
    }

    /**
     * @return Model|HasMany|object|null
     */
    public function hasApplicationFromUser($userId, $type)
    {
        return $this->applications()
            ->where('user_id', $userId)
            ->where('status', $type)
            ->first();
    }

    /**
     * @return Model|HasMany|object|null
     */
    public function hasSupportFromUser($userId)
    {
        return $this->supporters()->where('user_id', $userId)->first();
    }

    public function supporters(): HasMany
    {
        return $this->hasMany(IdeaSupporter::class, 'idea_id');
    }
}
