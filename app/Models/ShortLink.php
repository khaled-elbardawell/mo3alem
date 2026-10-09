<?php

namespace App\Models;

use Database\Factories\ShortLinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'title',
    'destination_url',
    'slug',
    'is_active',
    'expires_at',
    'version',
])]
class ShortLink extends Model
{
    /** @use HasFactory<ShortLinkFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'is_active' => true,
        'visit_count' => 0,
        'unique_visit_count' => 0,
        'version' => 1,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dailyStats(): HasMany
    {
        return $this->hasMany(ShortLinkDailyStat::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at?->isPast() ?? false;
    }

    public function isAvailableForRedirect(): bool
    {
        return ! $this->trashed() && $this->is_active && ! $this->isExpired();
    }

    protected function casts(): array
    {
        return [
            'destination_url' => 'encrypted',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
            'last_visited_at' => 'datetime',
        ];
    }
}
