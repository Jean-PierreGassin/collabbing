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

    public const COLLABORATION_STAGE_ROUGH_IDEA = 'rough_idea';

    public const COLLABORATION_STAGE_NEEDS_SHAPING = 'needs_shaping';

    public const COLLABORATION_STAGE_READY_TO_BUILD = 'ready_to_build';

    public const COLLABORATION_STAGE_ACTIVELY_BUILDING = 'actively_building';

    public const COLLABORATION_STAGE_LIVE = 'live';

    public const HELP_FRONTEND = 'frontend';

    public const HELP_BACKEND = 'backend';

    public const HELP_DESIGN = 'design';

    public const HELP_PRODUCT = 'product';

    public const HELP_TESTING = 'testing';

    public const HELP_DEVOPS = 'devops';

    public const HELP_WRITING = 'writing';

    public const HELP_RESEARCH = 'research';

    public const HELP_FEEDBACK = 'feedback';

    public const HELP_MARKETING = 'marketing';

    public const HELP_ANYTHING = 'anything';

    public const COMMUNICATION_STYLE_GITHUB = 'github';

    public const COMMUNICATION_STYLE_DISCORD = 'discord';

    public const COMMUNICATION_STYLE_SLACK = 'slack';

    public const COMMUNICATION_STYLE_EMAIL = 'email';

    public const COMMUNICATION_STYLE_CALLS = 'calls';

    public const COMMUNICATION_STYLE_NOT_DECIDED = 'not_decided_yet';

    protected $fillable = [
        'title',
        'tagline',
        'summary',
        'tags',
        'communication',
        'collaboration_stage',
        'help_wanted',
        'help_wanted_note',
        'first_contribution',
        'applications_open',
        'applications_closed_note',
        'communication_style',
        'communication_note',
        'getting_started_notes',
        'getting_started_notes_updated_at',
        'content',
        'status',
    ];

    protected $attributes = [
        'applications_open' => true,
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'help_wanted' => 'array',
            'applications_open' => 'boolean',
            'getting_started_notes_updated_at' => 'datetime',
        ];
    }

    public static function collaborationStages(): array
    {
        return [
            self::COLLABORATION_STAGE_ROUGH_IDEA,
            self::COLLABORATION_STAGE_NEEDS_SHAPING,
            self::COLLABORATION_STAGE_READY_TO_BUILD,
            self::COLLABORATION_STAGE_ACTIVELY_BUILDING,
            self::COLLABORATION_STAGE_LIVE,
        ];
    }

    public static function helpAreas(): array
    {
        return [
            self::HELP_FRONTEND,
            self::HELP_BACKEND,
            self::HELP_DESIGN,
            self::HELP_PRODUCT,
            self::HELP_TESTING,
            self::HELP_DEVOPS,
            self::HELP_WRITING,
            self::HELP_RESEARCH,
            self::HELP_FEEDBACK,
            self::HELP_MARKETING,
            self::HELP_ANYTHING,
        ];
    }

    public static function communicationStyles(): array
    {
        return [
            self::COMMUNICATION_STYLE_GITHUB,
            self::COMMUNICATION_STYLE_DISCORD,
            self::COMMUNICATION_STYLE_SLACK,
            self::COMMUNICATION_STYLE_EMAIL,
            self::COMMUNICATION_STYLE_CALLS,
            self::COMMUNICATION_STYLE_NOT_DECIDED,
        ];
    }

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
        return $this->applications()->where('status', IdeaApplication::STATUS_PENDING);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(IdeaApplication::class, 'idea_id');
    }

    public function approvedApplications(): HasMany
    {
        return $this->applications()->where('status', IdeaApplication::STATUS_APPROVED);
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
}
