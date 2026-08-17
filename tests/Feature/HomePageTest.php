<?php

use App\Models\DailyMetric;
use App\Models\User;
use App\Services\MetricService;
use Illuminate\Support\Facades\Cache;

test('the home page presents the muallem tools hub', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertViewIs('public.home')
        ->assertSee('معلم - الصفحة الرئيسية')
        ->assertSee(asset('assets/logo.png'), false)
        ->assertSee('class="h-14 w-auto brightness-0 invert"', false)
        ->assertSee('id="backToTopBtn"', false)
        ->assertSee('كل ما يحتاجه المعلم')
        ->assertSee('عجلة الأسماء العشوائية')
        ->assertSee('إنشاء QR احترافي')
        ->assertSee('إنشاء الشهادات')
        ->assertSee('مميزاتنا')
        ->assertSee('نشاط المنصة')
        ->assertSee('مرة تم تدوير العجلة')
        ->assertSee('اسمًا تمت إضافته')
        ->assertSee('عملية على رموز QR')
        ->assertSee('عملية على الشهادات')
        ->assertSee('خصّص الإعدادات')
        ->assertSee('أنشئ وشارك')
        ->assertSee('ما الأدوات المتوفرة في منصة معلم، ومتى أستخدم كل أداة؟')
        ->assertSee('ما فائدة إنشاء حساب في المنصة؟')
        ->assertSee('كيف تُحفظ بياناتي ومن يستطيع رؤيتها؟')
        ->assertDontSee('هل تعمل المنصة على الهاتف؟')
        ->assertDontSee('متى تتوفر أداتا QR والشهادات؟')
        ->assertDontSee('قريبًا')
        ->assertSee(route('tools.wheel'), false)
        ->assertDontSee('id="wheelCanvas"', false);
});

test('the features section presents four numbered feature cards', function () {
    $response = $this->get(route('home'))->assertSuccessful();

    foreach (['01', '02', '03', '04'] as $number) {
        $response->assertSee("data-feature-card=\"{$number}\"", false);
    }

    $featuresSection = (string) str($response->getContent())->after('id="features"')->before('id="activity"');

    expect(substr_count($response->getContent(), 'data-feature-number'))->toBe(4)
        ->and(substr_count($featuresSection, 'data-icon-tone="soft"'))->toBe(4)
        ->and(substr_count($response->getContent(), 'data-section-surface="plain"'))->toBe(2);
});

test('the platform activity section presents the four activity metrics', function () {
    DailyMetric::factory()->create([
        ...array_fill_keys(DailyMetric::COLUMNS, 0),
        'spins' => 1250,
        'names_saved' => 2500,
        'qr_generated' => 10,
        'qr_saved' => 4,
        'certificate_generated' => 1,
        'certificate_saved' => 5,
    ]);
    Cache::forget(MetricService::PLATFORM_ACTIVITY_CACHE_KEY);

    $response = $this->get(route('home'))->assertSuccessful();

    foreach (['spins', 'names', 'qr', 'certificates'] as $metric) {
        $response->assertSee("data-activity-card=\"{$metric}\"", false);
    }

    $activitySection = (string) str($response->getContent())->after('id="activity"')->before('id="how"');

    expect($response->viewData('platformActivity'))->toBe([
        'spins' => 1250,
        'names' => 2500,
        'qrOperations' => 14,
        'certificateOperations' => 6,
    ])->and(substr_count($activitySection, 'data-icon-tone="soft"'))->toBe(4)
        ->and($activitySection)->toContain('data-count-value="1250"')
        ->toContain('>+1,250</strong>');
});

test('platform activity cache is invalidated when a displayed metric changes', function () {
    Cache::put(MetricService::PLATFORM_ACTIVITY_CACHE_KEY, ['stale' => true], 300);

    app(MetricService::class)->increment('certificate_saved');

    expect(Cache::has(MetricService::PLATFORM_ACTIVITY_CACHE_KEY))->toBeFalse();
});

test('the home hero and tool cards link directly to the three tools', function () {
    $response = $this->get(route('home'))
        ->assertSuccessful()
        ->assertSeeInOrder(['data-primary-tools-action', 'data-guest-start'], false)
        ->assertSee('href="'.route('login').'" data-guest-start', false);

    foreach (['wheel', 'qr', 'certificates'] as $tool) {
        $response
            ->assertSee("data-hero-tool=\"{$tool}\"", false)
            ->assertSee("data-tool-card=\"{$tool}\"", false);
    }

    expect(substr_count($response->getContent(), 'motion-safe:animate-hero-tool-pulse'))->toBe(3);
});

test('the free start action is hidden from authenticated users', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('data-primary-tools-action', false)
        ->assertDontSee('data-guest-start', false);
});

test('the public header does not promote the wheel as a primary navigation item', function () {
    $response = $this->get(route('home'))->assertSuccessful();
    $header = (string) str($response->getContent())->between('<header', '</header>');

    expect($header)
        ->not->toContain('عجلة الأسماء')
        ->toContain('مميزاتنا');
});

test('the home page includes scroll-aware navigation and reveal animation hooks', function () {
    $response = $this->get(route('home'))->assertSuccessful();

    foreach (['home', 'tools', 'features', 'activity', 'how', 'faq'] as $target) {
        $response->assertSee("data-scrollspy-target=\"{$target}\"", false);
    }

    $response
        ->assertSee('data-reveal', false)
        ->assertSee('data-reveal-group', false);

    $script = file_get_contents(resource_path('js/app.js'));
    $styles = file_get_contents(resource_path('css/app.css'));

    expect($script)
        ->toContain('function setupPublicScrollSpy()')
        ->toContain('function setupPageRevealAnimations()')
        ->toContain('function setupActivityCountAnimations()')
        ->toContain('setupActivityCountAnimations();')
        ->toContain('function setupFaqToggleAnimations()')
        ->toContain('cubic-bezier(0.16, 1, 0.3, 1)')
        ->toContain('link.toggleAttribute("data-active", isActive)')
        ->toContain('new IntersectionObserver((entries) => {')
        ->and($styles)
        ->toContain('--animate-hero-tool-pulse')
        ->toContain('@keyframes hero-tool-pulse')
        ->toContain('html.reveal-ready [data-reveal]')
        ->toContain('@media (prefers-reduced-motion: reduce)');
});

test('the wheel uses the shared public site shell', function () {
    $response = $this->get(route('tools.wheel'))
        ->assertSuccessful()
        ->assertViewIs('public.tools.wheel')
        ->assertSee('id="wheelCanvas"', false)
        ->assertDontSee('class="stats-section', false)
        ->assertDontSee('class="faq-section', false)
        ->assertDontSee('id="faq"', false)
        ->assertSee('aria-label="التنقل الرئيسي"', false)
        ->assertSee('صُممت لتجعل يوم المعلم أسهل.')
        ->assertSee(asset('assets/logo.png'), false);

    expect(substr_count($response->getContent(), '<header'))->toBe(1)
        ->and(substr_count($response->getContent(), '<footer'))->toBe(1)
        ->and(substr_count($response->getContent(), 'id="backToTopBtn"'))->toBe(1)
        ->and((string) str($response->getContent())->between('<header', '</header>'))
        ->not->toContain('data-scrollspy-target');
});

test('the shared back to top control is initialized outside the wheel experience', function () {
    $script = file_get_contents(resource_path('js/app.js'));

    expect($script)
        ->toContain('function setupBackToTopButton()')
        ->toContain('setupBackToTopButton();')
        ->toContain('window.addEventListener("scroll", updateVisibility, { passive: true })')
        ->toContain('behavior: prefersReducedMotion ? "auto" : "smooth"');
});

test('legacy wheel query links redirect to the tool page', function () {
    $this->get(route('home', ['wheel' => 123, 'copy' => 1]))
        ->assertRedirectToRoute('tools.wheel', ['wheel' => 123, 'copy' => 1]);
});
