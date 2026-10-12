<?php

namespace App\Services;

use App\Models\SmartBoard;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SmartBoardService
{
    /** @param array{title: string, state: array<string, mixed>} $data */
    public function create(User $user, array $data): SmartBoard
    {
        if ($user->smartBoards()->count() >= (int) config('resource_limits.smart_boards', 20)) {
            throw ValidationException::withMessages([
                'title' => 'وصلت إلى الحد الأقصى للسبورات المحفوظة.',
            ]);
        }

        return $user->smartBoards()->create([
            ...$data,
            'last_opened_at' => now(),
        ]);
    }

    /** @param array<string, mixed> $data */
    public function update(SmartBoard $smartBoard, array $data): ?SmartBoard
    {
        $version = (int) $data['version'];
        unset($data['version']);

        $updated = SmartBoard::query()
            ->whereKey($smartBoard)
            ->where('version', $version)
            ->update([
                ...$data,
                'version' => $version + 1,
                'last_opened_at' => now(),
                'updated_at' => now(),
            ]);

        return $updated === 1 ? $smartBoard->fresh() : null;
    }

    public function delete(SmartBoard $smartBoard): void
    {
        $smartBoard->loadMissing('assets');

        foreach ($smartBoard->assets as $asset) {
            Storage::disk($asset->disk)->delete($asset->path);
        }

        $smartBoard->assets()->delete();
        $smartBoard->delete();
    }
}
