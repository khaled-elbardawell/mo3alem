<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class CertificateService
{
    public function __construct(private MetricService $metrics) {}

    /** @param array<string, mixed> $data */
    public function create(User $user, array $data, ?UploadedFile $background): Certificate
    {
        $backgroundPath = $data['template_key'] === 'custom'
            ? $background?->store('certificate-backgrounds', 'local')
            : null;

        try {
            return DB::transaction(function () use ($user, $data, $backgroundPath): Certificate {
                $lockedUser = User::query()->whereKey($user)->lockForUpdate()->firstOrFail();

                $maximumCertificates = (int) config('resource_limits.certificates', 5);

                if ($lockedUser->certificates()->count() >= $maximumCertificates) {
                    throw ValidationException::withMessages([
                        'title' => "وصلت إلى الحد الأقصى وهو {$maximumCertificates} شهادات محفوظة.",
                    ]);
                }

                $certificate = $lockedUser->certificates()->create([
                    'title' => $data['title'],
                    'template_key' => $data['template_key'],
                    'design' => $data['design'],
                    'background_path' => $backgroundPath,
                    'last_opened_at' => now(),
                ]);

                $this->metrics->increment('certificate_saved');

                return $certificate;
            });
        } catch (Throwable $throwable) {
            if ($backgroundPath) {
                Storage::disk('local')->delete($backgroundPath);
            }

            throw $throwable;
        }
    }

    /** @param array<string, mixed> $data */
    public function update(Certificate $certificate, array $data, ?UploadedFile $background): ?Certificate
    {
        $newBackgroundPath = $data['template_key'] === 'custom'
            ? $background?->store('certificate-backgrounds', 'local')
            : null;
        $oldBackgroundPath = $certificate->background_path;
        $usesBuiltInTemplate = $data['template_key'] !== 'custom';
        $castedDesign = (new Certificate)->forceFill(['design' => $data['design']])->getAttributes()['design'];
        $attributes = [
            'title' => $data['title'],
            'template_key' => $data['template_key'],
            'design' => $castedDesign,
            'version' => DB::raw('version + 1'),
            'last_opened_at' => now(),
            'updated_at' => now(),
        ];

        if ($newBackgroundPath || $usesBuiltInTemplate) {
            $attributes['background_path'] = $newBackgroundPath;
        }

        try {
            $updated = Certificate::query()
                ->whereKey($certificate)
                ->where('version', $data['version'])
                ->update($attributes);
        } catch (Throwable $throwable) {
            if ($newBackgroundPath) {
                Storage::disk('local')->delete($newBackgroundPath);
            }

            throw $throwable;
        }

        if ($updated === 0) {
            if ($newBackgroundPath) {
                Storage::disk('local')->delete($newBackgroundPath);
            }

            return null;
        }

        if (($newBackgroundPath || $usesBuiltInTemplate) && $oldBackgroundPath) {
            Storage::disk('local')->delete($oldBackgroundPath);
        }

        return $certificate->refresh();
    }
}
