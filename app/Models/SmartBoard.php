<?php

namespace App\Models;

use Database\Factories\SmartBoardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'title', 'state', 'version', 'last_opened_at'])]
class SmartBoard extends Model
{
    /** @use HasFactory<SmartBoardFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = ['version' => 1];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(SmartBoardAsset::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(SmartBoardSession::class);
    }

    protected function casts(): array
    {
        return [
            'state' => 'array',
            'version' => 'integer',
            'last_opened_at' => 'datetime',
        ];
    }
}
