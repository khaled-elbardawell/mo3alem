<?php

use App\Models\AdminAuditLog;
use App\Models\SiteSetting;
use App\Models\User;
use App\UserRole;

test('an administrator can open the site settings page', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->get(route('admin.settings.edit'))
        ->assertSuccessful()
        ->assertSee('إعدادات الموقع')
        ->assertSee('روابط التواصل والفوتر')
        ->assertSee('Facebook')
        ->assertSee('data-footer-links-editor', false);
});

test('an administrator can update footer links and the public footer reflects the change', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->get(route('home'))->assertSee('https://cmp-tch.com', false);

    $this->actingAs($admin)
        ->put(route('admin.settings.footer-links.update'), [
            'footer_links' => [
                [
                    'platform' => 'facebook',
                    'label' => 'صفحتنا على فيسبوك',
                    'url' => 'https://facebook.com/mo3alem-platform',
                    'open_in_new_tab' => '1',
                    'is_active' => '1',
                ],
                [
                    'platform' => 'instagram',
                    'label' => 'حساب غير ظاهر',
                    'url' => 'https://instagram.com/hidden-mo3alem-account',
                    'open_in_new_tab' => '1',
                    'is_active' => '0',
                ],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'تم حفظ روابط الفوتر بنجاح.');

    $setting = SiteSetting::query()->where('key', SiteSetting::SITE_KEY)->firstOrFail();

    expect($setting->footer_links)->toHaveCount(2)
        ->and($setting->footer_links[0]['platform'])->toBe('facebook')
        ->and($setting->footer_links[0]['is_active'])->toBeTrue()
        ->and(AdminAuditLog::query()->where('action', 'settings.footer-links.updated')->exists())->toBeTrue();

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('https://facebook.com/mo3alem-platform', false)
        ->assertSee('fa-brands fa-facebook-f', false)
        ->assertSee('target="_blank" rel="noopener noreferrer"', false)
        ->assertDontSee('https://instagram.com/hidden-mo3alem-account', false);
});

test('footer settings reject unsafe links and unsupported icons', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->from(route('admin.settings.edit'))
        ->put(route('admin.settings.footer-links.update'), [
            'footer_links' => [
                [
                    'platform' => 'custom-script',
                    'label' => 'رابط غير آمن',
                    'url' => 'javascript:alert(1)',
                    'open_in_new_tab' => '0',
                    'is_active' => '1',
                ],
            ],
        ])
        ->assertRedirect(route('admin.settings.edit'))
        ->assertSessionHasErrors([
            'footer_links.0.platform',
            'footer_links.0.url',
        ]);

    expect(SiteSetting::query()->doesntExist())->toBeTrue();
});

test('a non administrator cannot update footer settings', function () {
    $this->actingAs(User::factory()->create())
        ->put(route('admin.settings.footer-links.update'), ['footer_links' => []])
        ->assertForbidden();
});
