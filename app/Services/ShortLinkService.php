<?php

namespace App\Services;

use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShortLinkService
{
    private const RANDOM_SLUG_ATTEMPTS = 10;

    /** @param array<string, mixed> $data */
    public function create(User $user, array $data): ShortLink
    {
        return DB::transaction(function () use ($user, $data): ShortLink {
            $lockedUser = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            $maximumShortLinks = (int) config('resource_limits.short_links', 100);

            if ($lockedUser->shortLinks()->count() >= $maximumShortLinks) {
                throw ValidationException::withMessages([
                    'destination_url' => "وصلت إلى الحد الأقصى وهو {$maximumShortLinks} رابطًا مختصرًا محفوظًا.",
                ]);
            }

            $customAlias = $data['custom_alias'] ?? null;

            for ($attempt = 0; $attempt < self::RANDOM_SLUG_ATTEMPTS; $attempt++) {
                $slug = $customAlias ?: Str::lower(Str::random(7));

                try {
                    return $lockedUser->shortLinks()->create([
                        'title' => $this->title($data['title'] ?? null, $data['destination_url']),
                        'destination_url' => $data['destination_url'],
                        'slug' => $slug,
                        'is_active' => $data['is_active'],
                        'expires_at' => $data['expires_at'] ?? null,
                    ]);
                } catch (QueryException $exception) {
                    if (! $this->isUniqueConstraintViolation($exception)) {
                        throw $exception;
                    }

                    if ($customAlias) {
                        throw ValidationException::withMessages([
                            'custom_alias' => 'هذا المقطع مستخدم أو محجوز سابقًا.',
                        ]);
                    }
                }
            }

            throw ValidationException::withMessages([
                'custom_alias' => 'تعذر توليد مقطع فريد. حاول مرة أخرى.',
            ]);
        });
    }

    /** @param array<string, mixed> $data */
    public function update(ShortLink $shortLink, array $data): ?ShortLink
    {
        $castedAttributes = (new ShortLink)->forceFill([
            'destination_url' => $data['destination_url'],
        ])->getAttributes();

        $updated = ShortLink::query()
            ->whereKey($shortLink)
            ->where('version', $data['version'])
            ->update([
                'title' => $this->title($data['title'] ?? null, $data['destination_url']),
                'destination_url' => $castedAttributes['destination_url'],
                'is_active' => $data['is_active'],
                'expires_at' => $data['expires_at'] ?? null,
                'version' => DB::raw('version + 1'),
                'updated_at' => now(),
            ]);

        return $updated === 1 ? $shortLink->refresh() : null;
    }

    private function title(?string $title, string $destinationUrl): string
    {
        if (filled($title)) {
            return Str::limit($title, 120, '');
        }

        $host = (string) parse_url($destinationUrl, PHP_URL_HOST);

        return Str::limit($host ?: 'رابط مختصر', 120, '');
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        return in_array((string) $exception->getCode(), ['23000', '23505'], true)
            || Str::contains(Str::lower($exception->getMessage()), ['unique', 'duplicate']);
    }
}
