<?php

namespace App\Models;

use Database\Factories\AdDailyStatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ad_campaign_id', 'date', 'impressions', 'clicks'])]
class AdDailyStat extends Model
{
    /** @use HasFactory<AdDailyStatFactory> */
    use HasFactory;

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }

    protected function casts(): array
    {
        return ['date' => 'date'];
    }
}
