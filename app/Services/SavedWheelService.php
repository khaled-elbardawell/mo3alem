<?php

namespace App\Services;

use App\Models\SavedWheel;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SavedWheelService
{
    public function __construct(private MetricService $metrics) {}

    /**
     * @param  array{title:string,names:array<int,string>}  $data
     */
    public function create(User $user, array $data): SavedWheel
    {
        $this->ensureNamesAreWithinLimit($data['names']);

        return DB::transaction(function () use ($user, $data): SavedWheel {
            $lockedUser = User::query()->whereKey($user)->lockForUpdate()->firstOrFail();

            $maximumSavedWheels = (int) config('resource_limits.saved_wheels', 5);

            if ($lockedUser->savedWheels()->count() >= $maximumSavedWheels) {
                throw ValidationException::withMessages([
                    'title' => "وصلت إلى الحد الأقصى وهو {$maximumSavedWheels} قوائم محفوظة.",
                ]);
            }

            try {
                $savedWheel = $lockedUser->savedWheels()->create([
                    'title' => $data['title'],
                    'active_title' => $data['title'],
                    'names' => $data['names'],
                    'names_count' => count($data['names']),
                    'last_opened_at' => now(),
                ]);
            } catch (QueryException $exception) {
                if ($exception->getCode() === '23000') {
                    throw ValidationException::withMessages([
                        'title' => 'لديك قائمة نشطة بهذا الاسم بالفعل.',
                    ]);
                }

                throw $exception;
            }

            $this->metrics->increment('saved_wheels');

            if ($savedWheel->names_count > 0) {
                $this->metrics->increment('names_saved', $savedWheel->names_count);
            }

            return $savedWheel;
        });
    }

    /**
     * @param  array{title?:string,names?:array<int,string>,version:int}  $data
     */
    public function update(SavedWheel $savedWheel, array $data): ?SavedWheel
    {
        $previousNames = $savedWheel->names;

        if (array_key_exists('names', $data)) {
            $this->ensureNamesAreWithinLimit($data['names']);
        }

        $attributes = [
            'version' => DB::raw('version + 1'),
            'last_opened_at' => now(),
            'updated_at' => now(),
        ];

        foreach (['title', 'names'] as $key) {
            if (array_key_exists($key, $data)) {
                $attributes[$key] = is_array($data[$key])
                    ? json_encode($data[$key], JSON_THROW_ON_ERROR)
                    : $data[$key];
            }
        }

        if (array_key_exists('title', $data)) {
            $attributes['active_title'] = $data['title'];
        }

        if (array_key_exists('names', $data)) {
            $attributes['names_count'] = count($data['names']);
        }

        try {
            $updated = SavedWheel::query()
                ->whereKey($savedWheel)
                ->where('version', $data['version'])
                ->update($attributes);
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                throw ValidationException::withMessages([
                    'title' => 'لديك قائمة نشطة بهذا الاسم بالفعل.',
                ]);
            }

            throw $exception;
        }

        if ($updated === 0) {
            return null;
        }

        $savedWheel->refresh();

        if (array_key_exists('names', $data)) {
            $addedNamesCount = $this->addedNamesCount($previousNames, $savedWheel->names);

            if ($addedNamesCount > 0) {
                $this->metrics->increment('names_saved', $addedNamesCount);
            }
        }

        return $savedWheel;
    }

    public function addName(SavedWheel $savedWheel, string $name, int $version): ?SavedWheel
    {
        $names = $savedWheel->names;

        $maximumNames = (int) config('resource_limits.names_per_saved_wheel', 2000);

        if (count($names) >= $maximumNames) {
            throw ValidationException::withMessages([
                'name' => "وصلت القائمة إلى الحد الأقصى وهو {$maximumNames} اسم.",
            ]);
        }

        $names[] = $name;

        return $this->update($savedWheel, [
            'names' => $names,
            'version' => $version,
        ]);
    }

    public function removeName(SavedWheel $savedWheel, int $nameIndex, int $version): ?SavedWheel
    {
        $names = $savedWheel->names;

        if (! array_key_exists($nameIndex, $names)) {
            throw ValidationException::withMessages([
                'name' => 'الاسم المطلوب غير موجود أو حُذف مسبقًا.',
            ]);
        }

        array_splice($names, $nameIndex, 1);

        return $this->update($savedWheel, [
            'names' => $names,
            'version' => $version,
        ]);
    }

    public function restore(SavedWheel $savedWheel): SavedWheel
    {
        return DB::transaction(function () use ($savedWheel): SavedWheel {
            $lockedUser = User::query()->whereKey($savedWheel->user_id)->lockForUpdate()->firstOrFail();
            $maximumSavedWheels = (int) config('resource_limits.saved_wheels', 5);

            if ($lockedUser->savedWheels()->count() >= $maximumSavedWheels) {
                throw ValidationException::withMessages([
                    'title' => "لا يمكن استعادة القائمة لأن المستخدم وصل إلى الحد الأقصى وهو {$maximumSavedWheels} قوائم محفوظة.",
                ]);
            }

            if ($lockedUser->savedWheels()->where('active_title', $savedWheel->title)->exists()) {
                throw ValidationException::withMessages([
                    'title' => 'لا يمكن الاستعادة لأن لدى المستخدم قائمة نشطة بالاسم نفسه.',
                ]);
            }

            $savedWheel->restore();

            return $savedWheel->refresh();
        });
    }

    /** @param array<int, string> $names */
    private function ensureNamesAreWithinLimit(array $names): void
    {
        $maximumNames = (int) config('resource_limits.names_per_saved_wheel', 2000);

        if (count($names) > $maximumNames) {
            throw ValidationException::withMessages([
                'names' => "الحد الأقصى للقائمة هو {$maximumNames} اسم.",
            ]);
        }
    }

    /**
     * @param  array<int, string>  $previousNames
     * @param  array<int, string>  $currentNames
     */
    private function addedNamesCount(array $previousNames, array $currentNames): int
    {
        $previousCounts = array_count_values($previousNames);
        $currentCounts = array_count_values($currentNames);

        return collect($currentCounts)->sum(
            fn (int $count, int|string $name): int => max(0, $count - ($previousCounts[$name] ?? 0)),
        );
    }
}
