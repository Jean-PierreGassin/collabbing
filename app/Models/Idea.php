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
        'repository_html_url',
        'repository_default_branch',
        'repository_open_issues_count',
        'repository_stargazers_count',
        'repository_forks_count',
        'repository_latest_commit_sha',
        'repository_latest_commit_message',
        'repository_latest_commit_author',
        'repository_pushed_at',
        'repository_synced_at',
        'repository_missing_at',
    ];

    protected function casts(): array
    {
        return [
            'repository' => 'boolean',
            'repository_pushed_at' => 'datetime',
            'repository_synced_at' => 'datetime',
            'repository_missing_at' => 'datetime',
        ];
    }

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

    public function repositoryEvents(): HasMany
    {
        return $this->hasMany(IdeaRepositoryEvent::class, 'idea_id');
    }
}
