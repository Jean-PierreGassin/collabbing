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

        return $application instanceof IdeaApplication ? $application : null;
    }

    public function hasSupportFromUser(int|string $userId): ?IdeaSupporter
    {
        $supporter = $this->supporters()->where('user_id', $userId)->first();

        return $supporter instanceof IdeaSupporter ? $supporter : null;
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

        return $user instanceof User ? $user : null;
    }

    public function latestCodeRepository(): ?CodeRepository
    {
        $codeRepository = $this->codeRepository;

        return $codeRepository instanceof CodeRepository ? $codeRepository : null;
    }
}
