<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Idea
 * @package App
 */
class Idea extends Model
{
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

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(IdeaComment::class, 'idea_id');
    }

    /**
     * @return HasMany
     */
    public function pendingApplications(): HasMany
    {
        return $this->applications()->where('status', 'pending');
    }

    /**
     * @return HasMany
     */
    public function applications(): HasMany
    {
        return $this->hasMany(IdeaApplication::class, 'idea_id');
    }

    /**
     * @return HasMany
     */
    public function approvedApplications(): HasMany
    {
        return $this->applications()->where('status', 'approved');
    }

    /**
     * @param $userId
     * @param $type
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
     * @param $userId
     * @return Model|HasMany|object|null
     */
    public function hasSupportFromUser($userId)
    {
        return $this->supporters()->where('user_id', $userId)->first();
    }

    /**
     * @return HasMany
     */
    public function supporters(): HasMany
    {
        return $this->hasMany(IdeaSupporter::class, 'idea_id');
    }
}
