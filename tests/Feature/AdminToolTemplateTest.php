<?php

use App\Models\AdminAuditLog;
use App\Models\CertificateTemplate;
use App\Models\QrTemplate;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('an administrator can create update hide delete and restore a certificate template', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->post(route('admin.certificate-templates.store'), [
        'label' => 'قالب شهادة اختباري',
        'image' => UploadedFile::fake()->image('certificate.png', 1200, 800),
        'width' => 1200,
        'height' => 800,
        'sort_order' => 30,
        'is_active' => 1,
    ])->assertRedirect(route('admin.certificate-templates.index'));

    $template = CertificateTemplate::query()->where('label', 'قالب شهادة اختباري')->firstOrFail();

    Storage::disk('public')->assertExists($template->image_path);
    $this->get(route('tools.certificates'))
        ->assertSuccessful()
        ->assertSee('قالب شهادة اختباري');

    $this->actingAs($admin)->put(route('admin.certificate-templates.update', $template), [
        'label' => 'قالب شهادة مخفي',
        'width' => 1200,
        'height' => 800,
        'sort_order' => 31,
        'is_active' => 0,
    ])->assertRedirect(route('admin.certificate-templates.index'));

    $this->get(route('tools.certificates'))->assertDontSee('قالب شهادة مخفي');

    $this->actingAs($admin)->delete(route('admin.certificate-templates.destroy', $template))->assertRedirect();
    $this->assertSoftDeleted($template);

    $this->actingAs($admin)->patch(route('admin.certificate-templates.restore', $template->id))->assertRedirect();
    $this->assertNotSoftDeleted($template->fresh());

    expect(AdminAuditLog::query()->where('subject_type', $template->getMorphClass())->count())->toBe(4);
});

test('an administrator can create update delete and restore a qr template used by the public tool', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->post(route('admin.qr-templates.store'), [
        'label' => 'إطار QR اختباري',
        'image' => UploadedFile::fake()->image('qr-frame.png', 1000, 1000),
        'width' => 1000,
        'height' => 1000,
        'qr_x' => 200,
        'qr_y' => 250,
        'qr_size' => 500,
        'sort_order' => 30,
        'is_active' => 1,
    ])->assertRedirect(route('admin.qr-templates.index'));

    $template = QrTemplate::query()->where('label', 'إطار QR اختباري')->firstOrFail();

    Storage::disk('public')->assertExists($template->image_path);
    $response = $this->get(route('tools.qr'))->assertSuccessful()->assertSee('إطار QR اختباري');
    $configTemplate = collect($response->viewData('qrConfig')['templates'])->firstWhere('key', $template->key);

    expect($configTemplate)->toMatchArray([
        'width' => 1000,
        'height' => 1000,
        'qrX' => 200,
        'qrY' => 250,
        'qrSize' => 500,
    ]);

    $this->actingAs($admin)->put(route('admin.qr-templates.update', $template), [
        'label' => 'إطار QR معدل',
        'width' => 1000,
        'height' => 1000,
        'qr_x' => 220,
        'qr_y' => 240,
        'qr_size' => 500,
        'sort_order' => 31,
        'is_active' => 1,
    ])->assertRedirect(route('admin.qr-templates.index'));

    expect($template->fresh()->qr_x)->toBe(220);

    $this->actingAs($admin)->delete(route('admin.qr-templates.destroy', $template))->assertRedirect();
    $this->assertSoftDeleted($template);

    $this->actingAs($admin)->patch(route('admin.qr-templates.restore', $template->id))->assertRedirect();
    $this->assertNotSoftDeleted($template->fresh());
});

test('qr template validation keeps the code area inside the uploaded image', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->from(route('admin.qr-templates.create'))
        ->post(route('admin.qr-templates.store'), [
            'label' => 'إطار غير صالح',
            'image' => UploadedFile::fake()->image('invalid.png', 1000, 1000),
            'width' => 1000,
            'height' => 1000,
            'qr_x' => 700,
            'qr_y' => 700,
            'qr_size' => 500,
            'sort_order' => 1,
            'is_active' => 1,
        ])
        ->assertRedirect(route('admin.qr-templates.create'))
        ->assertSessionHasErrors(['qr_x', 'qr_y']);
});

test('regular users cannot manage tool templates', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.qr-templates.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.certificate-templates.index'))->assertForbidden();
});
