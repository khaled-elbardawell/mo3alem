<?php

namespace App\Models;

use Database\Factories\SmartBoardSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['smart_board_id', 'user_id', 'public_code', 'owner_token_hash', 'status', 'poll_key', 'poll_snapshot', 'version', 'expires_at'])]
#[Hidden(['owner_token_hash'])]
class SmartBoardSession extends Model
{
    /** @use HasFactory<SmartBoardSessionFactory> */
    use HasFactory;

    protected $attributes = ['status' => 'active', 'version' => 1];

    public function getRouteKeyName(): string
    {
        return 'public_code';
    }

    public function smartBoard(): BelongsTo
    {
        return $this->belongsTo(SmartBoard::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SmartBoardPollResponse::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }

    protected function casts(): array
    {
        return [
            'poll_snapshot' => 'array',
            'version' => 'integer',
            'expires_at' => 'datetime',
        ];
    }
}
