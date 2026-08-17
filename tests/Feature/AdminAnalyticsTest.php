<?php

use App\Models\AdCampaign;
use App\Models\Certificate;
use App\Models\Competition;
use App\Models\DailyMetric;
use App\Models\QrCode;
use App\Models\SavedWheel;
use App\Models\User;
use App\UserRole;
use Carbon\CarbonImmutable;

test('the admin dashboard shows current resources separately from todays activity', function () {
    $this->travelTo(CarbonImmutable::parse('2026-08-15 12:00:00'));

    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $user = User::factory()->create();
    Competition::factory()->for($user)->create();
    $deletedCompetition = Competition::factory()->for($user)->create();
    $deletedCompetition->delete();
    SavedWheel::factory()->for($user)->create();
    $deletedWheel = SavedWheel::factory()->for($user)->create();
    $deletedWheel->delete();
    QrCode::factory()->for($user)->create();
    Certificate::factory()->for($user)->create();
    AdCampaign::factory()->create();

    DailyMetric::factory()->create([
        ...array_fill_keys(DailyMetric::COLUMNS, 0),
        'date' => today()->toDateString(),
        'site_visits' => 12,
        'active_users' => 4,
        'competitions' => 3,
        'qr_generated' => 7,
        'certificate_saved' => 2,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'))->assertSuccessful();
    $metrics = $response->viewData('metrics');

    expect($response->viewData('today')->toDateString())->toBe('2026-08-15')
        ->and(DailyMetric::query()->firstOrFail()->date->toDateString())->toBe('2026-08-15')
        ->and((int) DailyMetric::query()->firstOrFail()->site_visits)->toBe(12)
        ->and($metrics)
        ->users->toBe(2)
        ->competitions->toBe(1)
        ->saved_wheels->toBe(1)
        ->qr_codes->toBe(1)
        ->certificates->toBe(1)
        ->site_visits_today->toBe(12)
        ->active_users_today->toBe(4)
        ->competitions_today->toBe(3)
        ->qr_generated_today->toBe(7)
        ->certificate_saved_today->toBe(2);

    $response
        ->assertSee('الحالة الحالية')
        ->assertSee('نشاط اليوم')
        ->assertSee(today()->locale('ar')->translatedFormat('l، j F Y'));
});

test('analytics includes every day every metric and readable arabic dates', function () {
    $this->travelTo(CarbonImmutable::parse('2026-08-15 12:00:00'));

    $admin = User::factory()->create(['role' => UserRole::Admin]);
    DailyMetric::factory()->create([
        ...array_fill_keys(DailyMetric::COLUMNS, 0),
        'date' => '2026-08-13',
        'site_visits' => 9,
        'competitions' => 2,
        'qr_generated' => 4,
        'certificate_saved' => 3,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.analytics', ['range' => '7']))
        ->assertSuccessful();

    $rows = $response->viewData('rows');
    $totals = $response->viewData('totals');

    expect($rows)->toHaveCount(7)
        ->and($rows->first()['date']->toDateString())->toBe('2026-08-15')
        ->and($rows->last()['date']->toDateString())->toBe('2026-08-09')
        ->and($rows->get(2)['site_visits'])->toBe(9)
        ->and($rows->get(1)['site_visits'])->toBe(0)
        ->and($totals['site_visits'])->toBe(9)
        ->and($totals['competitions'])->toBe(2)
        ->and($totals['qr_generated'])->toBe(4)
        ->and($totals['certificate_saved'])->toBe(3);

    $response
        ->assertSee('class="hidden max-h-[70vh] overflow-auto md:block"', false)
        ->assertSee('class="sticky top-0 right-0 z-30 min-w-48 bg-slate-50 p-3"', false)
        ->assertSee('class="sticky top-0 z-20 whitespace-nowrap bg-slate-50 p-3"', false)
        ->assertSee('رموز QR المُنشأة')
        ->assertSee('الشهادات المحفوظة')
        ->assertSee(CarbonImmutable::parse('2026-08-13')->locale('ar')->translatedFormat('l، j F Y'))
        ->assertDontSee('2026-08-13T00:00:00.000000Z');
});
