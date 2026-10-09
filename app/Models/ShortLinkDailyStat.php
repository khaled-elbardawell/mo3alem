<?php

namespace App\Models;

use Database\Factories\ShortLinkDailyStatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['short_link_id', 'date', 'visits', 'unique_visits'])]
class ShortLinkDailyStat extends Model
{
    /** @use HasFactory<ShortLinkDailyStatFactory> */
    use HasFactory;

    protected $attributes = [
        'visits' => 0,
        'unique_visits' => 0,
    ];

    public function shortLink(): BelongsTo
    {
        return $this->belongsTo(ShortLink::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
