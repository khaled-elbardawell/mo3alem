<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\SavedWheel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompetitionService
{
    public function __construct(
        private SavedWheelService $savedWheels,
        private MetricService $metrics,
    ) {}

    /**
     * @param  array{title:string,saved_wheel_id?:int|null,new_list_title?:string|null}  $data
     */
    public function create(User $user, array $data): Competition
    {
        return DB::transaction(function () use ($user, $data): Competition {
            $lockedUser = User::query()->whereKey($user)->lockForUpdate()->firstOrFail();

            if ($lockedUser->competitions()->count() >= 100) {
                throw ValidationException::withMessages([
                    'title' => 'وصلت إلى الحد الأقصى وهو 100 مسابقة.',
                ]);
            }

            $createsNewList = filled($data['new_list_title'] ?? null);
            $savedWheel = $createsNewList
                ? $this->savedWheels->create($lockedUser, [
                    'title' => $data['new_list_title'],
                    'names' => [],
                ])
                : SavedWheel::query()
                    ->whereBelongsTo($lockedUser)
                    ->whereKey($data['saved_wheel_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

            $competition = $lockedUser->competitions()->create([
                'saved_wheel_id' => $savedWheel->id,
                'title' => $data['title'],
                'names' => $savedWheel->names,
                'results' => [],
                'names_count' => $savedWheel->names_count,
                'results_count' => 0,
                'sync_source_list' => $createsNewList,
                'last_opened_at' => now(),
            ]);

            $this->metrics->increment('competitions');

            return $competition;
        });
    }

    /**
     * @param  array{title?:string,names?:array<int,string>,results?:array<int,array{round:int,name:string,date:string,position?:int|null}>,version:int}  $data
     */
    public function update(Competition $competition, array $data): ?Competition
    {
        return DB::transaction(function () use ($competition, $data): ?Competition {
            $currentResults = array_key_exists('results', $data) ? $data['results'] : $competition->results;
            $isNowActive = $competition->status === 'active' || $currentResults !== [];
            $shouldSyncSourceList = $competition->sync_source_list
                && ! $isNowActive
                && array_key_exists('names', $data);

            $attributes = [
                'version' => DB::raw('version + 1'),
                'status' => $isNowActive ? 'active' : 'draft',
                'sync_source_list' => $competition->sync_source_list && ! $isNowActive,
                'last_opened_at' => now(),
                'updated_at' => now(),
            ];

            foreach (['title', 'names', 'results'] as $key) {
                if (array_key_exists($key, $data)) {
                    $attributes[$key] = is_array($data[$key])
                        ? json_encode($data[$key], JSON_THROW_ON_ERROR)
                        : $data[$key];
                }
            }

            if (array_key_exists('names', $data)) {
                $attributes['names_count'] = count($data['names']);
            }

            if (array_key_exists('results', $data)) {
                $attributes['results_count'] = count($data['results']);
            }

            $updated = Competition::query()
                ->whereKey($competition)
                ->where('version', $data['version'])
                ->update($attributes);

            if ($updated === 0) {
                return null;
            }

            if ($shouldSyncSourceList && $competition->saved_wheel_id) {
                $sourceList = SavedWheel::query()
                    ->whereKey($competition->saved_wheel_id)
                    ->where('user_id', $competition->user_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->savedWheels->update($sourceList, [
                    'names' => $data['names'],
                    'version' => $sourceList->version,
                ]);
            }

            return $competition->refresh();
        });
    }

    public function addName(Competition $competition, string $name, int $version): ?Competition
    {
        $names = $competition->names;

        if (count($names) >= 2000) {
            throw ValidationException::withMessages([
                'name' => 'وصلت المسابقة إلى الحد الأقصى وهو 2000 اسم.',
            ]);
        }

        $names[] = $name;

        return $this->update($competition, [
            'names' => $names,
            'version' => $version,
        ]);
    }

    public function removeName(Competition $competition, int $nameIndex, int $version): ?Competition
    {
        $names = $competition->names;

        if (! array_key_exists($nameIndex, $names)) {
            throw ValidationException::withMessages([
                'name' => 'الاسم المطلوب غير موجود أو حُذف مسبقًا.',
            ]);
        }

        array_splice($names, $nameIndex, 1);

        return $this->update($competition, [
            'names' => $names,
            'version' => $version,
        ]);
    }
}
