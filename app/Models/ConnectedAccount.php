<?php

namespace App\Models;

use Database\Factories\ConnectedAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConnectedAccount extends Model
{
    /** @use HasFactory<ConnectedAccountFactory> */
    use HasFactory;

    protected $fillable = [
        'provider',
        'provider_user_id',
        'provider_username',
        'token',
        'scopes',
        'connected_at',
    ];

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
