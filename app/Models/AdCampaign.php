<?php

namespace App\Models;

use App\AdCampaignStatus;
use App\AdPlacement;
use Database\Factories\AdCampaignFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['title', 'image_path', 'target_url', 'alt_text', 'placement', 'status', 'starts_at', 'ends_at', 'weight'])]
class AdCampaign extends Model
{
    /** @use HasFactory<AdCampaignFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'status' => AdCampaignStatus::Draft->value,
        'weight' => 1,
    ];

    public function dailyStats(): HasMany
    {
        return $this->hasMany(AdDailyStat::class);
    }

    public function scopeEligible(Builder $query, ?AdPlacement $placement = null): Builder
    {
        return $query
            ->where('status', AdCampaignStatus::Active)
            ->when($placement, fn (Builder $query) => $query->where('placement', $placement))
            ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    protected function casts(): array
    {
        return [
            'placement' => AdPlacement::class,
            'status' => AdCampaignStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
