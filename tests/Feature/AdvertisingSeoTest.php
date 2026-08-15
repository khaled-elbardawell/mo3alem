<?php

use App\AdCampaignStatus;
use App\AdPlacement;
use App\Models\AdCampaign;
use App\Models\AdDailyStat;
use App\Models\DailyMetric;
use App\Models\SeoSetting;
use App\Models\UniqueMetricEvent;
use App\Models\User;
use App\Services\MetricService;
use App\Services\VisitorIdentity;
use App\UserRole;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

test('daily metrics can be incremented atomically', function () {
    app(MetricService::class)->increment('site_visits');

    expect(DailyMetric::query()->where('date', today()->toDateString())->value('site_visits'))->toBe(1);
});

test('unique metrics remain deduplicated after the cache is cleared', function () {
    $metrics = app(MetricService::class);

    expect($metrics->recordSiteVisit('same-visitor'))->toBeTrue();
    Cache::flush();
    expect($metrics->recordSiteVisit('same-visitor'))->toBeFalse();

    expect(DailyMetric::query()->where('date', today()->toDateString())->value('site_visits'))->toBe(1)
        ->and(UniqueMetricEvent::query()->count())->toBe(1);
});

test('an eligible campaign is displayed and unique impressions and clicks are counted', function () {
    $this->withCookie(VisitorIdentity::COOKIE_NAME, fake()->uuid());

    $campaign = AdCampaign::factory()->create([
        'placement' => AdPlacement::Top,
        'status' => AdCampaignStatus::Active,
        'target_url' => 'https://example.com/offer',
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee(route('ads.click', $campaign), false)
        ->assertSee(route('ads.impression', $campaign), false);

    expect(AdDailyStat::query()->whereBelongsTo($campaign, 'campaign')->value('impressions'))->toBeNull();

    $this->post(route('ads.impression', $campaign))->assertNoContent();
    $this->post(route('ads.impression', $campaign))->assertNoContent();

    $this->get(route('ads.click', $campaign))
        ->assertRedirect('https://example.com/offer');
    $this->get(route('ads.click', $campaign))
        ->assertRedirect('https://example.com/offer');

    $stats = AdDailyStat::query()->whereBelongsTo($campaign, 'campaign')->firstOrFail();
    $dailyMetrics = DailyMetric::query()->where('date', today()->toDateString())->firstOrFail();

    expect($stats->impressions)->toBe(1)
        ->and($stats->clicks)->toBe(1)
        ->and($dailyMetrics->ad_impressions)->toBe(1)
        ->and($dailyMetrics->ad_clicks)->toBe(1);
});

test('only campaigns matching placement status and schedule are displayed', function () {
    $eligible = AdCampaign::factory()->create([
        'placement' => AdPlacement::Top,
        'status' => AdCampaignStatus::Active,
        'starts_at' => now()->subMinute(),
        'ends_at' => now()->addMinute(),
    ]);
    $draft = AdCampaign::factory()->create([
        'placement' => AdPlacement::Top,
        'status' => AdCampaignStatus::Draft,
    ]);
    $future = AdCampaign::factory()->create([
        'placement' => AdPlacement::Top,
        'starts_at' => now()->addMinute(),
        'ends_at' => now()->addHour(),
    ]);
    $expired = AdCampaign::factory()->create([
        'placement' => AdPlacement::Top,
        'starts_at' => now()->subHour(),
        'ends_at' => now()->subMinute(),
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee(route('ads.click', $eligible), false)
        ->assertDontSee(route('ads.click', $draft), false)
        ->assertDontSee(route('ads.click', $future), false)
        ->assertDontSee(route('ads.click', $expired), false);
});

test('no unmanaged fallback advertisement is shown when no campaign is eligible', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee('المعرفة وراء الأدوات')
        ->assertDontSee('data-ad-impression-url', false);
});

test('a bottom campaign is rendered before the frequently asked questions', function () {
    $campaign = AdCampaign::factory()->create([
        'placement' => AdPlacement::Bottom,
        'status' => AdCampaignStatus::Active,
    ]);

    $content = $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee(route('ads.click', $campaign), false)
        ->assertSee(Storage::disk('public')->url($campaign->image_path), false)
        ->getContent();

    expect(strpos($content, route('ads.click', $campaign)))
        ->toBeLessThan(strpos($content, 'id="faq"'));
});

test('site visits are counted once per signed in visitor each day', function () {
    $this->withoutDefer();
    $firstVisitor = User::factory()->create();
    $secondVisitor = User::factory()->create();

    $this->actingAs($firstVisitor)->get(route('home'))->assertSuccessful();
    $this->actingAs($firstVisitor)->get(route('home'))->assertSuccessful();
    $this->actingAs($secondVisitor)->get(route('home'))->assertSuccessful();

    expect(DailyMetric::query()->where('date', today()->toDateString())->value('site_visits'))->toBe(2);
});

test('anonymous site visits are counted once per browser each day', function () {
    $this->withoutDefer();
    $this->withCookie(VisitorIdentity::COOKIE_NAME, fake()->uuid());

    $this->get(route('home'))->assertSuccessful();
    $this->get(route('home'))->assertSuccessful();

    expect(DailyMetric::query()->where('date', today()->toDateString())->value('site_visits'))->toBe(1);
});

test('expired campaigns reject impressions and clicks', function () {
    $campaign = AdCampaign::factory()->create([
        'status' => AdCampaignStatus::Active,
        'starts_at' => now()->subHour(),
        'ends_at' => now()->subMinute(),
    ]);

    $this->post(route('ads.impression', $campaign))->assertNotFound();
    $this->get(route('ads.click', $campaign))->assertNotFound();
});

test('seo settings are rendered in the homepage source and robots response', function () {
    SeoSetting::factory()->create([
        'title' => 'عنوان مخصص',
        'description' => 'وصف مخصص لمحركات البحث',
        'allow_indexing' => false,
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('<title>عنوان مخصص</title>', false)
        ->assertSee('noindex,nofollow', false);

    $this->get(route('robots'))
        ->assertSuccessful()
        ->assertSee('Disallow: /');
});

test('svg advertising uploads are rejected', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->post(route('admin.ad-campaigns.store'), [
            'title' => 'إعلان',
            'image' => UploadedFile::fake()->createWithContent('bad.svg', '<svg></svg>'),
            'target_url' => 'https://example.com',
            'alt_text' => 'إعلان تجريبي',
            'placement' => AdPlacement::Top->value,
            'status' => AdCampaignStatus::Active->value,
            'weight' => 1,
        ])
        ->assertSessionHasErrors('image');
});
