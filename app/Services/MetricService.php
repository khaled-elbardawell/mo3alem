<?php

namespace App\Services;

use App\Models\AdDailyStat;
use App\Models\DailyMetric;
use App\Models\QrCode;
use App\Models\QrCodeDailyStat;
use App\Models\UniqueMetricEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MetricService
{
    public function increment(string $column, int $amount = 1): void
    {
        if (! in_array($column, DailyMetric::COLUMNS, true) || $amount < 1) {
            throw new InvalidArgumentException('Invalid metric increment.');
        }

        $this->incrementForDate($column, $amount, today()->toDateString());
    }

    private function incrementForDate(string $column, int $amount, string $date): void
    {
        $timestamp = now();

        DB::transaction(function () use ($column, $amount, $date, $timestamp): void {
            DB::table((new DailyMetric)->getTable())->insertOrIgnore([
                'date' => $date,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            DailyMetric::query()
                ->where('date', $date)
                ->increment($column, $amount, ['updated_at' => $timestamp]);
        });
    }

    public function recordSiteVisit(string $visitorIdentifier): bool
    {
        return $this->recordUnique(
            "site-visit:{$this->identifierHash($visitorIdentifier)}",
            function (string $date): void {
                $this->incrementForDate('site_visits', 1, $date);
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
        $this->recordUnique(
            "active-user:{$user->id}",
            function (string $date): void {
                $this->incrementForDate('active_users', 1, $date);
            },
        );
    }

    public function recordQrScan(QrCode $qrCode, string $visitorIdentifier): void
    {
        $date = today()->toDateString();
        $timestamp = now();

        DB::transaction(function () use ($qrCode, $date, $timestamp): void {
            DB::table((new QrCodeDailyStat)->getTable())->insertOrIgnore([
                'qr_code_id' => $qrCode->id,
                'date' => $date,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            QrCodeDailyStat::query()
                ->whereBelongsTo($qrCode)
                ->where('date', $date)
                ->increment('scans', 1, ['updated_at' => $timestamp]);

            QrCode::query()->whereKey($qrCode)->increment('scan_count');
        });

        $this->recordUnique(
            "qr-scan:{$qrCode->id}:{$this->identifierHash($visitorIdentifier)}",
            function (string $uniqueDate) use ($qrCode): void {
                QrCodeDailyStat::query()
                    ->whereBelongsTo($qrCode)
                    ->where('date', $uniqueDate)
                    ->increment('unique_scans');

                QrCode::query()->whereKey($qrCode)->increment('unique_scan_count');
            },
        );
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
            function (string $date) use ($campaignId, $column): void {
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

                $metricColumn = $column === 'clicks' ? 'ad_clicks' : 'ad_impressions';
                $this->incrementForDate($metricColumn, 1, $date);
            },
        );
    }

    /**
     * @param  callable(string): void  $callback
     */
    private function recordUnique(string $eventKey, callable $callback): bool
    {
        $date = today()->toDateString();
        $eventHash = hash_hmac('sha256', $eventKey, (string) config('app.key'));

        return DB::transaction(function () use ($date, $eventHash, $callback): bool {
            $inserted = DB::table((new UniqueMetricEvent)->getTable())->insertOrIgnore([
                'date' => $date,
                'event_hash' => $eventHash,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($inserted === 0) {
                return false;
            }

            $callback($date);

            return true;
        });
    }

    private function identifierHash(string $visitorIdentifier): string
    {
        return hash_hmac('sha256', $visitorIdentifier, (string) config('app.key'));
    }
}
