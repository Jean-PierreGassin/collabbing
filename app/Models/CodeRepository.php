<?php

namespace App\Models;

use Database\Factories\CodeRepositoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CodeRepository extends Model
{
    public const PROVIDER_GITHUB = 'github';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_MISSING = 'missing';

    public const STATUS_PLANNED = 'planned';

    use HasFactory;

    protected $fillable = [
        'provider',
        'status',
        'provider_repository_id',
        'owner',
        'name',
        'full_name',
        'html_url',
        'default_branch',
        'open_issues_count',
        'stargazers_count',
        'forks_count',
        'latest_commit_sha',
        'latest_commit_message',
        'latest_commit_author',
        'pushed_at',
        'synced_at',
        'missing_at',
        'sync_due_at',
    ];

    protected static function newFactory(): CodeRepositoryFactory
    {
        return CodeRepositoryFactory::new();
    }

    protected function casts(): array
    {
        return [
            'pushed_at' => 'datetime',
            'synced_at' => 'datetime',
            'missing_at' => 'datetime',
            'sync_due_at' => 'datetime',
        ];
    }

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(RepositoryEvent::class);
    }

    public function isAvailable(): bool
    {
        return $this->exists && $this->status === self::STATUS_ACTIVE && $this->missing_at === null;
    }
}
