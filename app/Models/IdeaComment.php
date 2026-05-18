<?php

namespace App\Models;

use Database\Factories\IdeaCommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IdeaComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'idea_id',
        'parent_id',
        'content',
    ];

    protected static function newFactory(): IdeaCommentFactory
    {
        return IdeaCommentFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class, 'idea_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->oldest();
    }
}
