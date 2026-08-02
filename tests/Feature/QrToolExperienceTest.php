<?php

use App\AdCampaignStatus;
use App\AdPlacement;
use App\Models\AdCampaign;
use App\Models\QrCode;
use App\Models\User;

test('the qr tool presents an easy guest flow and all customization controls', function () {
    $response = $this->get(route('tools.qr'))
        ->assertSuccessful()
        ->assertSee('id="qrForm"', false)
        ->assertSee('name="content_type"', false)
        ->assertSee('value="url"', false)
        ->assertSee('value="text"', false)
        ->assertSee('value="wifi"', false)
        ->assertSee(asset('assets/qr-shapes/مربع كلاسيكي.png'), false)
        ->assertSee(asset('assets/qr-shapes/نقاط ودوائر.png'), false)
        ->assertSee(asset('assets/qr-shapes/مستديرة ناعم.png'), false)
        ->assertDontSee('value="extra-rounded"', false)
        ->assertDontSee('value="elegant"', false)
        ->assertDontSee(asset('assets/qr-shapes/مستدير جدا.png'), false)
        ->assertDontSee(asset('assets/qr-shapes/انيق.png'), false)
        ->assertSee('value="none" checked', false)
        ->assertSee('بدون قالب')
        ->assertDontSee('value="simple"', false)
        ->assertDontSee('value="card"', false)
        ->assertDontSee('value="education"', false)
        ->assertSee('id="qrPreviewImage"', false)
        ->assertSee('id="qrPreviewEmptyState"', false)
        ->assertSee('ابدأ بكتابة المحتوى')
        ->assertSee('لا يوجد شيء للمعاينة حتى الآن.')
        ->assertDontSee('value="https://cmp-tch.com"', false)
        ->assertDontSee('id="qrAuthenticatedActions"', false)
        ->assertDontSee('id="qrMyCodesLink"', false)
        ->assertDontSee('id="createNewQrLink"', false)
        ->assertSee('id="generateQrBtn"', false)
        ->assertSee('id="guestCloudSaveCard"', false)
        ->assertSee('حفظ هذا التصميم مجانًا')
        ->assertSee('PNG')
        ->assertSee('SVG');

    collect(range(1, 11))->each(fn (int $templateNumber) => $response
        ->assertSee('value="template-'.$templateNumber.'"', false)
        ->assertSee(asset('assets/qr-templates/'.$templateNumber.'.png'), false));
});

test('top side and bottom campaigns appear in the qr tool', function () {
    $campaigns = collect(AdPlacement::cases())->mapWithKeys(fn (AdPlacement $placement): array => [
        $placement->value => AdCampaign::factory()->create([
            'placement' => $placement,
            'status' => AdCampaignStatus::Active,
        ]),
    ]);

    $response = $this->get(route('tools.qr'))->assertSuccessful();

    $campaigns->each(fn (AdCampaign $campaign) => $response
        ->assertSee(route('ads.click', $campaign), false)
        ->assertSee(route('ads.impression', $campaign), false));
});

test('a saved qr can be reopened in the editor by its owner', function () {
    $user = User::factory()->create();
    $qrCode = QrCode::factory()->for($user)->create([
        'title' => 'رمز النشاط المحفوظ',
        'payload' => ['url' => 'https://example.com/saved'],
    ]);

    $response = $this->actingAs($user)->get(route('tools.qr', ['qr' => $qrCode]));

    $response->assertSuccessful()
        ->assertSee('حسابك جاهز للحفظ السحابي')
        ->assertSee('id="qrAuthenticatedActions"', false)
        ->assertSee('id="qrMyCodesLink"', false)
        ->assertSee('id="createNewQrLink"', false)
        ->assertSee(route('dashboard', ['section' => 'qr']), false)
        ->assertSee('رموزي')
        ->assertSee('إنشاء رمز جديد');
    expect($response->viewData('qrConfig')['savedQrCode'])
        ->toMatchArray([
            'id' => $qrCode->id,
            'title' => 'رمز النشاط المحفوظ',
            'payload' => ['url' => 'https://example.com/saved'],
        ]);
});

test('the qr account handoff preserves the intended editor destination', function () {
    $this->get(route('tools.qr.auth', 'register'))
        ->assertRedirect(route('register'));

    expect(session('url.intended'))->toBe(route('tools.qr'));
});

test('the dashboard includes a separate searchable qr section', function () {
    $user = User::factory()->create();
    $qrCode = QrCode::factory()->for($user)->create(['title' => 'رمز درس الكسور']);

    $this->actingAs($user)
        ->get(route('dashboard', ['section' => 'qr', 'search' => 'الكسور']))
        ->assertSuccessful()
        ->assertSee('رموز QR')
        ->assertSee('رمز درس الكسور')
        ->assertSee(route('tools.qr', ['qr' => $qrCode]), false);
});

test('the qr client keeps the draft through registration and supports both downloads', function () {
    $script = file_get_contents(resource_path('js/qr-tool.js'));

    expect($script)
        ->toContain('muallem-qr-draft-v1')
        ->toContain('pendingSave')
        ->toContain('localStorage.setItem')
        ->toContain('downloadQrPng')
        ->toContain('downloadQrSvg')
        ->toContain('new ClipboardItem')
        ->toContain('qr_generate')
        ->toContain('qrForm.addEventListener("change", schedulePreview)')
        ->toContain('showEmptyPreview')
        ->toContain('loadTemplateDataUrl')
        ->toContain('appendTemplate')
        ->toContain('getElementById("createNewQrLink")')
        ->toContain('localStorage.removeItem(draftKey)')
        ->not->toContain('افتح النشاط');
});

test('the public hub footer and sitemap link to the qr tool', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee(route('tools.qr'), false);

    $this->get(route('sitemap'))
        ->assertSuccessful()
        ->assertSee(e(route('tools.qr')), false);
});
