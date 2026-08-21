<?php

use App\Models\AdminAuditLog;
use App\Models\SeoSetting;
use App\Models\User;
use App\SeoPage;
use App\UserRole;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('administrators can manage seo settings for every public page', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->get(route('admin.seo.edit', ['page' => SeoPage::Qr->value]))
        ->assertSuccessful()
        ->assertSee('مركز إدارة SEO')
        ->assertSee('الصفحة الرئيسية')
        ->assertSee('عجلة الأسماء')
        ->assertSee('إنشاء رمز QR')
        ->assertSee('إنشاء الشهادات')
        ->assertSee('إنشاء رمز QR احترافي | معلم');
});

test('each public page renders its own seo and social metadata', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->put(route('admin.seo.update', SeoPage::Wheel->value), seoPayload([
            'title' => 'عنوان مخصص للعجلة',
            'description' => 'وصف مخصص لصفحة عجلة الأسماء فقط.',
            'canonical_url' => 'https://example.com/random-wheel',
            'allow_following' => '0',
            'og_title' => 'عنوان المشاركة للعجلة',
            'og_description' => 'وصف المشاركة للعجلة',
            'og_image_alt' => 'عجلة أسماء ملونة',
        ]))
        ->assertRedirect(route('admin.seo.edit', ['page' => SeoPage::Wheel->value]));

    $wheelSetting = SeoSetting::query()->where('page_key', SeoPage::Wheel->value)->firstOrFail();

    expect($wheelSetting->title)->toBe('عنوان مخصص للعجلة')
        ->and($wheelSetting->allow_following)->toBeFalse()
        ->and(AdminAuditLog::query()->where('action', 'seo.updated')->where('subject_id', $wheelSetting->id)->exists())->toBeTrue();

    $this->get(route('tools.wheel'))
        ->assertSuccessful()
        ->assertSee('<title>عنوان مخصص للعجلة</title>', false)
        ->assertSee('<meta name="robots" content="index,nofollow">', false)
        ->assertSee('<link rel="canonical" href="https://example.com/random-wheel">', false)
        ->assertSee('<meta property="og:title" content="عنوان المشاركة للعجلة">', false)
        ->assertSee('<meta name="twitter:description" content="وصف المشاركة للعجلة">', false);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee('عنوان مخصص للعجلة');
});

test('noindex and sitemap controls apply to one page without affecting other pages', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->put(route('admin.seo.update', SeoPage::Qr->value), seoPayload([
            'title' => 'صفحة QR غير مفهرسة',
            'allow_indexing' => '0',
            'include_in_sitemap' => '1',
        ]))
        ->assertSessionHasNoErrors();

    $this->get(route('tools.qr'))
        ->assertSuccessful()
        ->assertSee('<meta name="robots" content="noindex,follow">', false);

    $this->get(route('sitemap'))
        ->assertSuccessful()
        ->assertDontSee(route('tools.qr'))
        ->assertSee(route('home'))
        ->assertSee(route('tools.wheel'));
});

test('administrators can upload and remove a page social image', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->put(route('admin.seo.update', SeoPage::Certificates->value), seoPayload([
            'og_image' => UploadedFile::fake()->image('social.jpg', 1200, 630),
        ]))
        ->assertSessionHasNoErrors();

    $setting = SeoSetting::query()->where('page_key', SeoPage::Certificates->value)->firstOrFail();
    $imagePath = $setting->og_image_path;

    Storage::disk('public')->assertExists($imagePath);

    $this->actingAs($admin)
        ->put(route('admin.seo.update', SeoPage::Certificates->value), seoPayload([
            'remove_og_image' => '1',
        ]))
        ->assertSessionHasNoErrors();

    expect($setting->refresh()->og_image_path)->toBeNull();
    Storage::disk('public')->assertMissing($imagePath);
});

/** @param array<string, mixed> $overrides */
function seoPayload(array $overrides = []): array
{
    return [
        'site_name' => 'معلم',
        'title' => 'عنوان الصفحة الاحترافي',
        'description' => 'وصف احترافي وواضح للصفحة مخصص لمحركات البحث.',
        'keywords' => 'معلم، أدوات تعليمية، صفحة',
        'canonical_url' => null,
        'allow_indexing' => '1',
        'allow_following' => '1',
        'og_title' => null,
        'og_description' => null,
        'og_image_alt' => null,
        'remove_og_image' => '0',
        'twitter_card' => 'summary_large_image',
        'include_in_sitemap' => '1',
        'sitemap_change_frequency' => 'weekly',
        'sitemap_priority' => '0.9',
        ...$overrides,
    ];
}
