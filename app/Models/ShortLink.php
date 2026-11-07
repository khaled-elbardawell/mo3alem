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
    'qr_design',
])]
class ShortLink extends Model
{
    /** @var array{style: string, foreground_color: string, eye_color: string, background_color: string} */
    public const DEFAULT_QR_DESIGN = [
        'style' => 'classic',
        'foreground_color' => '#111827',
        'eye_color' => '#6d28d9',
        'background_color' => '#ffffff',
    ];

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

    /** @return array{style: string, foreground_color: string, eye_color: string, background_color: string} */
    public function resolvedQrDesign(): array
    {
        return [...self::DEFAULT_QR_DESIGN, ...($this->qr_design ?? [])];
    }

    protected function casts(): array
    {
        return [
            'destination_url' => 'encrypted',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
            'last_visited_at' => 'datetime',
            'qr_design' => 'array',
        ];
    }
}
