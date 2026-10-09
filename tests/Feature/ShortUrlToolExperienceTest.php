<?php

use App\Models\ShortLink;
use App\Models\ShortLinkDailyStat;
use App\Models\User;

test('the public short url page explains the tool and asks guests to sign in', function () {
    $this->get(route('tools.short-url'))
        ->assertSuccessful()
        ->assertViewIs('public.tools.short-url')
        ->assertSee('data-tool-page-header', false)
        ->assertSee('أداة اختصار الروابط')
        ->assertSee(route('tools.short-url.auth', 'login'), false)
        ->assertSee(route('tools.short-url.auth', 'register'), false)
        ->assertDontSee('action="'.route('short-links.store').'"', false);
});

test('short url authentication actions remember the tool as the intended destination', function (string $action) {
    $this->get(route('tools.short-url.auth', $action))
        ->assertRedirect(route($action));

    expect(session('url.intended'))->toBe(route('tools.short-url'));
})->with(['login', 'register']);

test('logging in from the short url tool returns to the tool', function () {
    $user = User::factory()->create();

    $this->get(route('tools.short-url.auth', 'login'));

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('tools.short-url'));

    $this->assertAuthenticatedAs($user);
});

test('registering from the short url tool returns to the tool', function () {
    $this->get(route('tools.short-url.auth', 'register'));

    $this->post(route('register'), [
        'name' => 'مستخدم جديد',
        'email' => 'short-url-user@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('tools.short-url'));

    $this->assertAuthenticated();
});

test('a verified user sees the creation form and its latest result', function () {
    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->create([
        'title' => 'رابط الصف',
        'slug' => 'class-link',
    ]);

    $this->actingAs($user)
        ->get(route('tools.short-url', ['short_link' => $shortLink]))
        ->assertSuccessful()
        ->assertSee('action="'.route('short-links.store').'"', false)
        ->assertSee(route('short-links.redirect', $shortLink->slug), false)
        ->assertSee(route('short-links.qr', $shortLink), false)
        ->assertSee('data-download-qr="png"', false)
        ->assertSee('data-download-qr="svg"', false)
        ->assertSee(route('user.short-links.show', $shortLink), false);
});

test('the dashboard lists searches and sorts the current users short links', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $popular = ShortLink::factory()->for($user)->create([
        'title' => 'رابط العلوم',
        'slug' => 'science-class',
        'visit_count' => 50,
    ]);
    ShortLink::factory()->for($user)->create([
        'title' => 'رابط الرياضيات',
        'slug' => 'math-class',
        'visit_count' => 5,
    ]);
    ShortLink::factory()->for($otherUser)->create(['title' => 'رابط مستخدم آخر']);

    $this->actingAs($user)
        ->get(route('dashboard', ['section' => 'links', 'search' => 'science', 'sort' => 'visits']))
        ->assertSuccessful()
        ->assertSee('روابطي')
        ->assertSee('رابط العلوم')
        ->assertSee('/short/science-class')
        ->assertSee(route('user.short-links.show', $popular), false)
        ->assertDontSee('رابط الرياضيات')
        ->assertDontSee('رابط مستخدم آخر');
});

test('the detail page renders selected period analytics and management actions', function () {
    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->create([
        'title' => 'رابط الإحصائيات',
        'slug' => 'analytics-link',
        'visit_count' => 15,
        'unique_visit_count' => 8,
    ]);
    ShortLinkDailyStat::factory()->for($shortLink)->create([
        'date' => today(),
        'visits' => 6,
        'unique_visits' => 4,
    ]);

    $this->actingAs($user)
        ->get(route('user.short-links.show', ['shortLink' => $shortLink, 'period' => '7']))
        ->assertSuccessful()
        ->assertViewHas('period', '7')
        ->assertSee('إجمالي الزيارات')
        ->assertSee('زيارات فريدة يوميًا')
        ->assertSee('data-visit-chart', false)
        ->assertSee('data-chart-series="visits"', false)
        ->assertSee('data-chart-series="unique-visits"', false)
        ->assertSee('عرض جدول البيانات')
        ->assertSee(route('short-links.update', $shortLink), false)
        ->assertSee(route('short-links.destroy', $shortLink), false)
        ->assertSee('data-copy-short-link', false)
        ->assertSee('data-download-qr="png"', false);
});

test('the short link javascript shares qr conversion helpers with the qr tool', function () {
    $shortLinkScript = file_get_contents(resource_path('js/short-links.js'));
    $qrToolScript = file_get_contents(resource_path('js/qr-tool.js'));
    $sharedScript = file_get_contents(resource_path('js/qr-downloads.js'));

    expect($shortLinkScript)
        ->toContain('svgToPngBlob')
        ->toContain('data-download-qr')
        ->and($qrToolScript)
        ->toContain('from "./qr-downloads"')
        ->and($sharedScript)
        ->toContain('canvas.toBlob')
        ->toContain('image/svg+xml');
});
