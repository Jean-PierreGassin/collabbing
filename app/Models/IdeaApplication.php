<?php

namespace App\Models;

use Database\Factories\IdeaApplicationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IdeaApplication extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_WITHDRAWN = 'withdrawn';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_LEFT = 'left';

    public const STATUS_REMOVED = 'removed';

    protected $fillable = [
        'user_id',
        'idea_id',
        'content',
        'contribution_type',
        'first_action',
        'approval_note',
        'decline_reason',
        'status',
        'withdrawn_at',
        'left_at',
        'removed_at',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    protected function casts(): array
    {
        return [
            'withdrawn_at' => 'datetime',
            'left_at' => 'datetime',
            'removed_at' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_WITHDRAWN,
            self::STATUS_APPROVED,
            self::STATUS_DECLINED,
            self::STATUS_LEFT,
            self::STATUS_REMOVED,
        ];
    }

    public static function activeStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
        ];
    }

    protected static function newFactory(): IdeaApplicationFactory
    {
        return IdeaApplicationFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class, 'idea_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(IdeaApplicationMessage::class, 'idea_application_id')
            ->oldest('occurred_at')
            ->oldest('id');
    }

    public function readStates(): HasMany
    {
        return $this->hasMany(IdeaApplicationReadState::class, 'idea_application_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::activeStatuses());
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isFinal(): bool
    {
        return in_array($this->status, [
            self::STATUS_WITHDRAWN,
            self::STATUS_DECLINED,
            self::STATUS_APPROVED,
            self::STATUS_LEFT,
            self::STATUS_REMOVED,
        ], true);
    }
}
