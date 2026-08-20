<?php

namespace App\Models;

use Database\Factories\SavedWheelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'title', 'active_title', 'names', 'names_count', 'version', 'last_opened_at'])]
class SavedWheel extends Model
{
    /** @use HasFactory<SavedWheelFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'names_count' => 0,
        'version' => 1,
    ];

    /** @var array<int, string>|null */
    private ?array $pendingNames = null;

    protected static function booted(): void
    {
        static::saved(function (SavedWheel $savedWheel): void {
            if ($savedWheel->pendingNames !== null) {
                $names = $savedWheel->pendingNames;
                $savedWheel->pendingNames = null;
                $savedWheel->replaceNames($names);
            }
        });

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

    public function nameEntries(): HasMany
    {
        return $this->hasMany(SavedWheelName::class);
    }

    /** @return array<int, string> */
    public function getNamesAttribute(mixed $legacyNames = null): array
    {
        if ($this->pendingNames !== null) {
            return $this->pendingNames;
        }

        if (is_string($legacyNames)) {
            return json_decode($legacyNames, true, flags: JSON_THROW_ON_ERROR);
        }

        $entries = $this->relationLoaded('nameEntries')
            ? $this->getRelation('nameEntries')
            : $this->nameEntries()->orderBy('position')->get();

        return $entries->sortBy('position')->pluck('name')->values()->all();
    }

    /** @param array<int, string>|string|null $names */
    public function setNamesAttribute(array|string|null $names): void
    {
        $this->pendingNames = is_string($names)
            ? json_decode($names, true, flags: JSON_THROW_ON_ERROR)
            : array_values($names ?? []);
    }

    /** @param array<int, string> $names */
    public function replaceNames(array $names): void
    {
        $timestamp = now();
        $rows = collect(array_values($names))->map(fn (string $name, int $position): array => [
            'saved_wheel_id' => $this->getKey(),
            'name' => $name,
            'position' => $position,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ])->all();

        if ($rows !== []) {
            SavedWheelName::query()->upsert(
                $rows,
                ['saved_wheel_id', 'position'],
                ['name', 'updated_at'],
            );
        }

        $this->nameEntries()->where('position', '>=', count($names))->delete();
        $this->setRelation('nameEntries', $this->nameEntries()->orderBy('position')->get());
    }

    protected function casts(): array
    {
        return [
            'last_opened_at' => 'datetime',
        ];
    }
}
