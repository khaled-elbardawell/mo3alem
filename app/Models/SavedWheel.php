<?php

namespace App\Models;

use Database\Factories\SavedWheelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'title', 'active_title', 'names', 'results', 'names_count', 'version', 'last_opened_at'])]
class SavedWheel extends Model
{
    /** @use HasFactory<SavedWheelFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'names' => '[]',
        'results' => '[]',
        'names_count' => 0,
        'version' => 1,
    ];

    protected static function booted(): void
    {
        static::deleting(function (SavedWheel $savedWheel): void {
            if (! $savedWheel->isForceDeleting()) {
                $savedWheel->forceFill(['active_title' => null])->saveQuietly();
            }
        });

        static::restoring(function (SavedWheel $savedWheel): void {
            $savedWheel->active_title = $savedWheel->title;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'names' => 'array',
            'results' => 'array',
            'last_opened_at' => 'datetime',
        ];
    }
}
