<?php

namespace App\Services;

use App\Models\AdDailyStat;
use App\Models\DailyMetric;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class MetricService
{
    private const COLUMNS = [
        'site_visits',
        'registrations',
        'active_users',
        'saved_wheels',
        'names_saved',
        'spins',
        'imports',
        'qr_generated',
        'qr_saved',
        'ad_impressions',
        'ad_clicks',
    ];

    public function increment(string $column, int $amount = 1): void
    {
        if (! in_array($column, self::COLUMNS, true) || $amount < 1) {
            throw new InvalidArgumentException('Invalid metric increment.');
        }

        $date = now()->toDateString();

        DB::table((new DailyMetric)->getTable())->upsert(
            [[
                'date' => $date,
                'created_at' => now(),
                'updated_at' => now(),
            ]],
            ['date'],
            ['updated_at'],
        );

        DailyMetric::query()->where('date', $date)->increment($column, $amount);
    }

    public function recordSiteVisit(string $visitorIdentifier): bool
    {
        return $this->recordUnique(
            "site-visit:{$this->identifierHash($visitorIdentifier)}",
            function (): void {
                $this->increment('site_visits');
            },
        );
    }

    public function recordAdImpression(int $campaignId, string $visitorIdentifier): bool
    {
        return $this->recordUniqueAdEvent($campaignId, 'impressions', $visitorIdentifier);
    }

    public function recordAdClick(int $campaignId, string $visitorIdentifier): bool
    {
        return $this->recordUniqueAdEvent($campaignId, 'clicks', $visitorIdentifier);
    }

    public function recordActiveUser(User $user): void
    {
        $date = now()->toDateString();

        if (Cache::add("metric:active-user:{$date}:{$user->id}", true, now()->endOfDay())) {
            $this->increment('active_users');
        }
    }

    private function recordUniqueAdEvent(int $campaignId, string $column, string $visitorIdentifier): bool
    {
        if (! in_array($column, ['impressions', 'clicks'], true)) {
            throw new InvalidArgumentException('Invalid advertising metric.');
        }

        $event = $column === 'clicks' ? 'ad-click' : 'ad-impression';
        $identifierHash = $this->identifierHash($visitorIdentifier);

        return $this->recordUnique(
            "{$event}:{$campaignId}:{$identifierHash}",
            function () use ($campaignId, $column): void {
                $date = now()->toDateString();

                DB::transaction(function () use ($campaignId, $column, $date): void {
                    DB::table((new AdDailyStat)->getTable())->insertOrIgnore([
                        'ad_campaign_id' => $campaignId,
                        'date' => $date,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    AdDailyStat::query()
                        ->where('ad_campaign_id', $campaignId)
                        ->where('date', $date)
                        ->increment($column);

                    $this->increment($column === 'clicks' ? 'ad_clicks' : 'ad_impressions');
                });
            },
        );
    }

    /**
     * @param  callable(): void  $callback
     */
    private function recordUnique(string $eventKey, callable $callback): bool
    {
        $date = now()->toDateString();
        $cacheKey = "metric:unique:{$date}:{$eventKey}";

        if (! Cache::add($cacheKey, true, now()->endOfDay())) {
            return false;
        }

        try {
            $callback();
        } catch (Throwable $throwable) {
            Cache::forget($cacheKey);

            throw $throwable;
        }

        return true;
    }

    private function identifierHash(string $visitorIdentifier): string
    {
        return hash_hmac('sha256', $visitorIdentifier, (string) config('app.key'));
    }
}
