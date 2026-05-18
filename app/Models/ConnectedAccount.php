<?php

namespace App\Models;

use Database\Factories\ConnectedAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConnectedAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'provider_username',
        'token',
        'scopes',
        'connected_at',
    ];

    protected static function newFactory(): ConnectedAccountFactory
    {
        return ConnectedAccountFactory::new();
    }

    protected function casts(): array
    {
        return [
            'token' => 'encrypted',
            'scopes' => 'array',
            'connected_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
