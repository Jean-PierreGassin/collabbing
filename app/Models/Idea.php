<?php

namespace App\Models;

use Database\Factories\IdeaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Idea extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_PAUSED = 'suspended';

    public const STATUS_SHIPPED = 'expired';

    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_PAUSED,
        self::STATUS_SHIPPED,
        self::STATUS_CLOSED,
    ];

    protected $fillable = [
        'title',
        'summary',
        'communication',
        'content',
        'status',
    ];

    protected static function newFactory(): IdeaFactory
    {
        return IdeaFactory::new();
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

    public function hasApplicationFromUser(int|string $userId, string $type): ?IdeaApplication
    {
        $application = $this->applications()
            ->where('user_id', $userId)
            ->where('status', $type)
            ->first();

        if (! $application instanceof IdeaApplication) {
            return null;
        }

        return $application;
    }

    public function hasSupportFromUser(int|string $userId): ?IdeaSupporter
    {
        $supporter = $this->supporters()->where('user_id', $userId)->first();

        if (! $supporter instanceof IdeaSupporter) {
            return null;
        }

        return $supporter;
    }

    public function supporters(): HasMany
    {
        return $this->hasMany(IdeaSupporter::class, 'idea_id');
    }

    public function codeRepositories(): HasMany
    {
        return $this->hasMany(CodeRepository::class, 'idea_id');
    }

    public function codeRepository(): HasOne
    {
        return $this->hasOne(CodeRepository::class, 'idea_id')->latestOfMany();
    }

    public function owner(): ?User
    {
        $user = $this->user;

        if (! $user instanceof User) {
            return null;
        }

        return $user;
    }

    public function latestCodeRepository(): ?CodeRepository
    {
        $codeRepository = $this->codeRepository;

        if (! $codeRepository instanceof CodeRepository) {
            return null;
        }

        return $codeRepository;
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }
}
