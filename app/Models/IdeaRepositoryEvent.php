<?php

namespace App\Models;

use Database\Factories\IdeaRepositoryEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdeaRepositoryEvent extends Model
{
    /** @use HasFactory<IdeaRepositoryEventFactory> */
    use HasFactory;

    protected $fillable = [
        'idea_id',
        'type',
        'summary',
        'occurred_at',
        'dedupe_key',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class);
    }
}
