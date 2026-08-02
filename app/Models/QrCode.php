<?php

namespace App\Models;

use Database\Factories\QrCodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'title', 'content_type', 'payload', 'design', 'logo_path', 'version', 'last_opened_at'])]
#[Hidden(['logo_path'])]
class QrCode extends Model
{
    /** @use HasFactory<QrCodeFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'version' => 1,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'payload' => 'encrypted:array',
            'design' => 'array',
            'last_opened_at' => 'datetime',
        ];
    }
}
