<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\CompetitionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'names_count' => 0,
        'results_count' => 0,
        'version' => 1,
        'status' => 'draft',
        'sync_source_list' => false,
    ];

    /** @var array<int, string>|null */
    private ?array $pendingNames = null;

    /** @var array<int, array<string, mixed>>|null */
    private ?array $pendingResults = null;

    protected static function booted(): void
    {
        static::saved(function (Competition $competition): void {
            if ($competition->pendingNames !== null) {
                $names = $competition->pendingNames;
                $competition->pendingNames = null;
                $competition->replaceParticipants($names);
            }

            if ($competition->pendingResults !== null) {
                $results = $competition->pendingResults;
                $competition->pendingResults = null;
                $competition->replaceResults($results);
            }

            $competition->pruneInactiveParticipants();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function savedWheel(): BelongsTo
    {
        return $this->belongsTo(SavedWheel::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CompetitionParticipant::class);
    }

    public function activeParticipants(): HasMany
    {
        return $this->hasMany(CompetitionParticipant::class)->where('is_active', true);
    }

    public function resultEntries(): HasMany
    {
        return $this->hasMany(CompetitionResult::class);
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

        $participants = $this->relationLoaded('activeParticipants')
            ? $this->getRelation('activeParticipants')
            : $this->activeParticipants()->orderBy('position')->get();

        return $participants->sortBy('position')->pluck('name')->values()->all();
    }

    /** @param array<int, string>|string|null $names */
    public function setNamesAttribute(array|string|null $names): void
    {
        $this->pendingNames = is_string($names)
            ? json_decode($names, true, flags: JSON_THROW_ON_ERROR)
            : array_values($names ?? []);
    }

    /** @return array<int, array{round:int,name:string,date:string,position:int|null}> */
    public function getResultsAttribute(mixed $legacyResults = null): array
    {
        if ($this->pendingResults !== null) {
            return $this->pendingResults;
        }

        if (is_string($legacyResults)) {
            return json_decode($legacyResults, true, flags: JSON_THROW_ON_ERROR);
        }

        $results = $this->relationLoaded('resultEntries')
            ? $this->getRelation('resultEntries')
            : $this->resultEntries()->orderBy('sort_order')->get();

        return $results->sortBy('sort_order')->map(fn (CompetitionResult $result): array => [
            'round' => $result->round,
            'name' => $result->name_snapshot,
            'date' => $result->won_at->toISOString(),
            'position' => $result->position,
        ])->values()->all();
    }

    /** @param array<int, array<string, mixed>>|string|null $results */
    public function setResultsAttribute(array|string|null $results): void
    {
        $this->pendingResults = is_string($results)
            ? json_decode($results, true, flags: JSON_THROW_ON_ERROR)
            : array_values($results ?? []);
    }

    /** @param array<int, string> $names */
    public function replaceParticipants(array $names): void
    {
        $participants = $this->participants()
            ->orderByDesc('is_active')
            ->orderBy('position')
            ->orderBy('id')
            ->get();
        $participantQueues = [];

        foreach ($participants as $participant) {
            $participantQueues[$participant->name][] = $participant;
        }

        $usedParticipantIds = [];

        foreach (array_values($names) as $position => $name) {
            $participantQueue = $participantQueues[$name] ?? [];
            /** @var CompetitionParticipant|null $participant */
            $participant = array_shift($participantQueue);
            $participantQueues[$name] = $participantQueue;

            if (! $participant) {
                $participant = $this->participants()->create([
                    'name' => $name,
                    'position' => $position,
                    'is_active' => true,
                ]);
            } elseif (! $participant->is_active || $participant->position !== $position) {
                $participant->update([
                    'position' => $position,
                    'is_active' => true,
                ]);
            }

            $usedParticipantIds[] = $participant->id;
        }

        $this->participants()
            ->where('is_active', true)
            ->when($usedParticipantIds !== [], fn ($query) => $query->whereNotIn('id', $usedParticipantIds))
            ->update(['is_active' => false]);

        $this->setRelation(
            'activeParticipants',
            $this->activeParticipants()->orderBy('position')->get(),
        );
    }

    /** @param array<int, array<string, mixed>> $results */
    public function replaceResults(array $results): void
    {
        $existingResults = $this->resultEntries()->get()->keyBy('round');
        $participants = $this->participants()->get();
        $retainedResultIds = [];

        foreach (array_values($results) as $sortOrder => $resultData) {
            $round = (int) $resultData['round'];
            $position = isset($resultData['position']) ? (int) $resultData['position'] : null;
            $name = (string) $resultData['name'];
            $participant = $participants
                ->filter(fn (CompetitionParticipant $participant): bool => $participant->name === $name)
                ->first(fn (CompetitionParticipant $participant): bool => $position === null
                    ? $participant->is_active
                    : $participant->position === $position - 1);
            $attributes = [
                'competition_participant_id' => $participant?->id,
                'sort_order' => $sortOrder,
                'name_snapshot' => $name,
                'position' => $position,
                'won_at' => CarbonImmutable::parse($resultData['date'])->setTimezone(config('app.timezone')),
            ];
            /** @var CompetitionResult|null $result */
            $result = $existingResults->get($round);

            if ($result) {
                $result->update($attributes);
            } else {
                $result = $this->resultEntries()->create([
                    'round' => $round,
                    ...$attributes,
                ]);
            }

            $retainedResultIds[] = $result->id;
        }

        $this->resultEntries()
            ->when($retainedResultIds !== [], fn ($query) => $query->whereNotIn('id', $retainedResultIds))
            ->delete();

        $this->setRelation(
            'resultEntries',
            $this->resultEntries()->orderBy('sort_order')->get(),
        );
    }

    public function pruneInactiveParticipants(): void
    {
        $this->participants()
            ->where('is_active', false)
            ->whereDoesntHave('results')
            ->delete();
    }

    protected function casts(): array
    {
        return [
            'sync_source_list' => 'boolean',
            'last_opened_at' => 'datetime',
        ];
    }
}
