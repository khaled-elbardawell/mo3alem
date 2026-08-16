<?php

namespace App\Models;

use Database\Factories\QrCodeDailyStatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['qr_code_id', 'date', 'scans', 'unique_scans'])]
class QrCodeDailyStat extends Model
{
    /** @use HasFactory<QrCodeDailyStatFactory> */
    use HasFactory;

    protected $attributes = [
        'scans' => 0,
        'unique_scans' => 0,
    ];

    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
