<?php

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
        ->assertSee('رموز QR تم إنشاؤها')
        ->assertSee('شهادات تم تصميمها')
        ->assertSee('خصّص الإعدادات')
        ->assertSee('أنشئ وشارك')
        ->assertDontSee('متى تتوفر أداتا QR والشهادات؟')
        ->assertDontSee('قريبًا')
        ->assertSee(route('tools.wheel'), false)
        ->assertDontSee('id="wheelCanvas"', false);
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
        ->toContain('link.toggleAttribute("data-active", isActive)')
        ->toContain('new IntersectionObserver((entries) => {')
        ->and($styles)
        ->toContain('html.reveal-ready [data-reveal]')
        ->toContain('@media (prefers-reduced-motion: reduce)');
});

test('the wheel uses the shared public site shell', function () {
    $response = $this->get(route('tools.wheel'))
        ->assertSuccessful()
        ->assertViewIs('public.tools.wheel')
        ->assertSee('id="wheelCanvas"', false)
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
