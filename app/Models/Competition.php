<?php

namespace App\Models;

use Database\Factories\CompetitionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'saved_wheel_id',
    'title',
    'names',
    'results',
    'names_count',
    'results_count',
    'version',
    'status',
    'sync_source_list',
    'last_opened_at',
])]
class Competition extends Model
{
    /** @use HasFactory<CompetitionFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'names' => '[]',
        'results' => '[]',
        'names_count' => 0,
        'results_count' => 0,
        'version' => 1,
        'status' => 'draft',
        'sync_source_list' => false,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function savedWheel(): BelongsTo
    {
        return $this->belongsTo(SavedWheel::class);
    }

    protected function casts(): array
    {
        return [
            'names' => 'array',
            'results' => 'array',
            'sync_source_list' => 'boolean',
            'last_opened_at' => 'datetime',
        ];
    }
}
