<?php

namespace App\Services;

use App\Models\QrCode;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class QrCodeService
{
    public function __construct(private MetricService $metrics) {}

    /** @param array<string, mixed> $data */
    public function create(User $user, array $data, ?UploadedFile $logo): QrCode
    {
        $logoPath = $logo?->store('qr-logos', 'local');

        try {
            return DB::transaction(function () use ($user, $data, $logoPath): QrCode {
                $lockedUser = User::query()->whereKey($user)->lockForUpdate()->firstOrFail();

                if ($lockedUser->qrCodes()->count() >= 100) {
                    throw ValidationException::withMessages([
                        'title' => 'وصلت إلى الحد الأقصى وهو 100 رمز QR محفوظ.',
                    ]);
                }

                $qrCode = $lockedUser->qrCodes()->create([
                    'title' => $data['title'],
                    'content_type' => $data['content_type'],
                    'payload' => $data['payload'],
                    'design' => $data['design'],
                    'logo_path' => $logoPath,
                    'last_opened_at' => now(),
                ]);

                $this->metrics->increment('qr_saved');

                return $qrCode;
            });
        } catch (Throwable $throwable) {
            if ($logoPath) {
                Storage::disk('local')->delete($logoPath);
            }

            throw $throwable;
        }
    }

    /** @param array<string, mixed> $data */
    public function update(QrCode $qrCode, array $data, ?UploadedFile $logo): ?QrCode
    {
        $newLogoPath = $logo?->store('qr-logos', 'local');
        $oldLogoPath = $qrCode->logo_path;
        $removeLogo = (bool) ($data['remove_logo'] ?? false);
        $castedAttributes = (new QrCode)->forceFill([
            'payload' => $data['payload'],
            'design' => $data['design'],
        ])->getAttributes();
        $attributes = [
            'title' => $data['title'],
            'content_type' => $data['content_type'],
            'payload' => $castedAttributes['payload'],
            'design' => $castedAttributes['design'],
            'version' => DB::raw('version + 1'),
            'last_opened_at' => now(),
            'updated_at' => now(),
        ];

        if ($newLogoPath || $removeLogo) {
            $attributes['logo_path'] = $newLogoPath;
        }

        try {
            $updated = QrCode::query()
                ->whereKey($qrCode)
                ->where('version', $data['version'])
                ->update($attributes);
        } catch (Throwable $throwable) {
            if ($newLogoPath) {
                Storage::disk('local')->delete($newLogoPath);
            }

            throw $throwable;
        }

        if ($updated === 0) {
            if ($newLogoPath) {
                Storage::disk('local')->delete($newLogoPath);
            }

            return null;
        }

        if (($newLogoPath || $removeLogo) && $oldLogoPath) {
            Storage::disk('local')->delete($oldLogoPath);
        }

        return $qrCode->refresh();
    }
}
