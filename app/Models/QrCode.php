<?php

namespace App\Models;

use App\QrCodeMode;
use Database\Factories\QrCodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'title', 'mode', 'content_type', 'payload', 'design', 'logo_path', 'public_code', 'is_active', 'expires_at', 'version', 'last_opened_at'])]
#[Hidden(['logo_path'])]
class QrCode extends Model
{
    /** @use HasFactory<QrCodeFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'mode' => 'static',
        'is_active' => true,
        'scan_count' => 0,
        'unique_scan_count' => 0,
        'version' => 1,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dailyStats(): HasMany
    {
        return $this->hasMany(QrCodeDailyStat::class);
    }

    public function isAvailableForRedirect(): bool
    {
        return $this->mode === QrCodeMode::Dynamic
            && ! $this->trashed()
            && $this->is_active
            && (! $this->expires_at || $this->expires_at->isFuture());
    }

    protected function casts(): array
    {
        return [
            'mode' => QrCodeMode::class,
            'payload' => 'encrypted:array',
            'design' => 'array',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
            'last_opened_at' => 'datetime',
        ];
    }
}
