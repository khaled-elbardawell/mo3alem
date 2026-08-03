<?php

namespace App\Models;

use Database\Factories\CertificateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'title', 'template_key', 'design', 'background_path', 'version', 'last_opened_at'])]
#[Hidden(['background_path'])]
class Certificate extends Model
{
    /** @use HasFactory<CertificateFactory> */
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
            'design' => 'encrypted:array',
            'last_opened_at' => 'datetime',
        ];
    }
}
