<?php

namespace App\Models;

use Database\Factories\IdeaSupporterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdeaSupporter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'idea_id',
    ];

    protected static function newFactory(): IdeaSupporterFactory
    {
        return IdeaSupporterFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class, 'idea_id');
    }
}
