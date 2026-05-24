<?php

namespace App\Models;

use Database\Factories\IdeaApplicationReadStateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdeaApplicationReadState extends Model
{
    use HasFactory;

    protected $fillable = [
        'idea_application_id',
        'user_id',
        'last_read_at',
    ];

    protected function casts(): array
    {
        return [
            'last_read_at' => 'datetime',
        ];
    }

    protected static function newFactory(): IdeaApplicationReadStateFactory
    {
        return IdeaApplicationReadStateFactory::new();
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
