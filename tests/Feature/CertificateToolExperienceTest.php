<?php

use App\AdCampaignStatus;
use App\AdPlacement;
use App\Models\AdCampaign;
use App\Models\Certificate;
use App\Models\User;

test('the certificate tool presents templates upload editing preview and print controls', function () {
    $response = $this->get(route('tools.certificates'))
        ->assertSuccessful()
        ->assertSee('id="certificateCanvas"', false)
        ->assertSee('id="certificateBackgroundInput"', false)
        ->assertSee('accept="image/png,image/jpeg,image/webp"', false)
        ->assertDontSee('image/svg+xml', false)
        ->assertSee('id="certificateRibbon"', false)
        ->assertSee('id="certificateRibbonScroller"', false)
        ->assertSee('id="certificateEditorShell"', false)
        ->assertSee('id="certificateEditorRail"', false)
        ->assertSee('id="certificateTemplatesPanel"', false)
        ->assertSee('id="certificateLayersPanel"', false)
        ->assertSee('data-certificate-sidebar-panel="templates"', false)
        ->assertSee('data-certificate-sidebar-panel="properties"', false)
        ->assertSee('data-certificate-sidebar-panel="layers"', false)
        ->assertSee('id="certificateTextControls"', false)
        ->assertSee('id="createNewCertificateLink"', false)
        ->assertSee('شهاداتي')
        ->assertDontSee('> الخصائص</a>', false)
        ->assertSee('id="addCertificateTextBtn"', false)
        ->assertSee('id="certificateLayersList"', false)
        ->assertSee('id="certificatePropertiesForm"', false)
        ->assertDontSee('id="certificateLayerUpBtn"', false)
        ->assertDontSee('id="certificateLayerDownBtn"', false)
        ->assertSee('id="certificateCanvasToolbar"', false)
        ->assertSee('data-certificate-history-controls', false)
        ->assertSee('id="certificateUndoBtn"', false)
        ->assertSee('aria-label="تراجع"', false)
        ->assertSee('id="certificateRedoBtn"', false)
        ->assertSee('aria-label="إعادة"', false)
        ->assertSee('id="saveCertificateBtn"', false)
        ->assertSee('id="printCertificateBtn"', false)
        ->assertSee('id="certificateFullscreenBtn"', false)
        ->assertSee('id="certificatePreviewDialog"', false)
        ->assertSee('id="guestCertificateSaveCard"', false)
        ->assertSee('حفظ هذه الشهادة مجاناً');

    collect(range(1, 11))->each(function (int $templateNumber) use ($response): void {
        $extension = $templateNumber <= 5 ? 'png' : 'svg';
        $response
            ->assertSee('data-certificate-template="b'.$templateNumber.'"', false)
            ->assertSee(asset("assets/certificate-templates/b{$templateNumber}.{$extension}"), false);
    });
});

test('top side and bottom campaigns appear in the certificate tool', function () {
    $campaigns = collect(AdPlacement::cases())->mapWithKeys(fn (AdPlacement $placement): array => [
        $placement->value => AdCampaign::factory()->create([
            'placement' => $placement,
            'status' => AdCampaignStatus::Active,
        ]),
    ]);

    $response = $this->get(route('tools.certificates'))->assertSuccessful();

    $campaigns->each(fn (AdCampaign $campaign) => $response
        ->assertSee(route('ads.click', $campaign), false)
        ->assertSee(route('ads.impression', $campaign), false));
});

test('a saved certificate can be reopened in the editor only by its owner', function () {
    $user = User::factory()->create();
    $intruder = User::factory()->create();
    $certificate = Certificate::factory()->for($user)->create(['title' => 'شهادة العلوم المحفوظة']);

    $response = $this->actingAs($user)->get(route('tools.certificates', ['certificate' => $certificate]));

    $response->assertSuccessful()
        ->assertSee('شهاداتي')
        ->assertSee(route('dashboard', ['section' => 'certificates']), false);
    expect($response->viewData('certificateConfig')['savedCertificate'])
        ->toMatchArray([
            'id' => $certificate->id,
            'title' => 'شهادة العلوم المحفوظة',
            'template_key' => 'b6',
        ]);

    $this->actingAs($intruder)
        ->get(route('tools.certificates', ['certificate' => $certificate]))
        ->assertForbidden();
});

test('the certificate account handoff preserves the intended editor destination', function () {
    $this->get(route('tools.certificates.auth', 'register'))
        ->assertRedirect(route('register'));

    expect(session('url.intended'))->toBe(route('tools.certificates'));
});

test('the dashboard includes a searchable certificates section', function () {
    $user = User::factory()->create();
    $certificate = Certificate::factory()->for($user)->create(['title' => 'شهادة درس الكسور']);

    $this->actingAs($user)
        ->get(route('dashboard', ['section' => 'certificates', 'search' => 'الكسور']))
        ->assertSuccessful()
        ->assertSee('شهاداتي')
        ->assertSee('شهادة درس الكسور')
        ->assertSee(route('tools.certificates', ['certificate' => $certificate]), false);
});

test('the certificate client supports draft handoff manipulation and printable export', function () {
    $script = file_get_contents(resource_path('js/certificate-tool.js'));

    expect($script)
        ->toContain('muallem-certificate-draft-v1')
        ->toContain('pendingSave')
        ->toContain('localStorage.setItem')
        ->toContain('startElementPointerOperation')
        ->toContain('showSidebarPanel("properties"')
        ->toContain('showSidebarPanel("templates"')
        ->toContain('data-certificate-sidebar-panel')
        ->toContain('usesCommandKey && event.key.toLowerCase() === "z"')
        ->toContain('usesCommandKey && event.key.toLowerCase() === "y"')
        ->toContain('event.key === "Delete"')
        ->toContain('editorShell.requestFullscreen()')
        ->toContain('resizeElement')
        ->toContain('renderOutputCanvas')
        ->toContain('downloadCertificatePngBtn')
        ->toContain('printWindow.print()')
        ->toContain('certificate_generate')
        ->toContain('document.fonts?.ready');
});

test('the public hub footer and sitemap link to the certificate tool', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee(route('tools.certificates'), false);

    $this->get(route('sitemap'))
        ->assertSuccessful()
        ->assertSee(e(route('tools.certificates')), false);
});
