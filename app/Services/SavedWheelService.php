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
        return DB::transaction(function () use ($user, $data): SavedWheel {
            $lockedUser = User::query()->whereKey($user)->lockForUpdate()->firstOrFail();

            if ($lockedUser->savedWheels()->count() >= 100) {
                throw ValidationException::withMessages([
                    'title' => 'وصلت إلى الحد الأقصى وهو 100 قائمة.',
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

        if (array_key_exists('names', $data) && $savedWheel->names_count > 0) {
            $this->metrics->increment('names_saved', $savedWheel->names_count);
        }

        return $savedWheel;
    }

    public function addName(SavedWheel $savedWheel, string $name, int $version): ?SavedWheel
    {
        $names = $savedWheel->names;

        if (count($names) >= 2000) {
            throw ValidationException::withMessages([
                'name' => 'وصلت القائمة إلى الحد الأقصى وهو 2000 اسم.',
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
}
