<?php

namespace App\Models;

use Database\Factories\IdeaApplicationMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdeaApplicationMessage extends Model
{
    use HasFactory;

    public const TYPE_MESSAGE = 'message';

    public const TYPE_SYSTEM = 'system';

    public const TYPE_APPROVED = 'approved';

    public const TYPE_DECLINED = 'declined';

    public const TYPE_WITHDRAWN = 'withdrawn';

    public const TYPE_LEFT = 'left';

    public const TYPE_REMOVED = 'removed';

    protected $fillable = [
        'idea_application_id',
        'user_id',
        'type',
        'body',
        'occurred_at',
    ];

    protected $attributes = [
        'type' => self::TYPE_MESSAGE,
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }

    protected static function newFactory(): IdeaApplicationMessageFactory
    {
        return IdeaApplicationMessageFactory::new();
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(IdeaApplication::class, 'idea_application_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
