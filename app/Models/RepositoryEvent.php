<?php

namespace App\Models;

use Database\Factories\RepositoryEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepositoryEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'summary',
        'occurred_at',
        'dedupe_key',
        'payload',
    ];

    protected static function newFactory(): RepositoryEventFactory
    {
        return RepositoryEventFactory::new();
    }

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function codeRepository(): BelongsTo
    {
        return $this->belongsTo(CodeRepository::class);
    }
}
