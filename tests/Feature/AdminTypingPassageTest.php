<?php

use App\Models\AdminAuditLog;
use App\Models\TypingPassage;
use App\Models\User;
use App\Services\TypingPassageCalculator;
use App\TypingPassageLanguage;
use App\UserRole;

test('tailwind scans the Laravel pagination templates', function () {
    expect(file_get_contents(resource_path('css/app.css')))
        ->toContain('@source "../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php";');
});

test('non administrators cannot manage typing passages', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.typing-passages.index'))
        ->assertForbidden();
});

test('administrators can browse and filter typing passages', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $arabic = TypingPassage::factory()->arabic()->create();
    TypingPassage::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.typing-passages.index', ['language' => 'ar', 'search' => 'الطالب']))
        ->assertSuccessful()
        ->assertSee($arabic->content)
        ->assertSee('فقرات التدريب');
});

test('administrators can create a passage with automatically calculated time', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $content = 'ينظم الطالب أفكاره في خطوات واضحة ثم يراجع كل خطوة قبل مشاركة الحل مع زملائه داخل الصف.';

    $this->actingAs($admin)
        ->post(route('admin.typing-passages.store'), [
            'language' => TypingPassageLanguage::Arabic->value,
            'content' => $content,
            'auto_calculate_time' => '1',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.typing-passages.index'));

    $passage = TypingPassage::query()->sole();
    expect($passage->expected_seconds)->toBe(app(TypingPassageCalculator::class)->expectedSeconds($content))
        ->and($passage->word_count)->toBe(17)
        ->and($passage->is_active)->toBeTrue()
        ->and(AdminAuditLog::query()->where('action', 'typing-passage.created')->exists())->toBeTrue();
});

test('administrators can update a passage with a manual time', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $passage = TypingPassage::factory()->create();
    $content = 'Students organize their ideas into clear steps and review every answer before sharing it with classmates during the lesson.';

    $this->actingAs($admin)
        ->put(route('admin.typing-passages.update', $passage), [
            'language' => 'en',
            'content' => $content,
            'auto_calculate_time' => '0',
            'expected_seconds' => 75,
            'is_active' => '0',
        ])
        ->assertRedirect(route('admin.typing-passages.index'));

    expect($passage->refresh())
        ->content->toBe($content)
        ->expected_seconds->toBe(75)
        ->is_active->toBeFalse()
        ->and(AdminAuditLog::query()->where('action', 'typing-passage.updated')->exists())->toBeTrue();
});

test('duplicate normalized passage content is rejected', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $passage = TypingPassage::factory()->arabic()->create();

    $this->actingAs($admin)
        ->from(route('admin.typing-passages.create'))
        ->post(route('admin.typing-passages.store'), [
            'language' => 'ar',
            'content' => "  {$passage->content}  ",
            'auto_calculate_time' => '1',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.typing-passages.create'))
        ->assertSessionHasErrors('content');
});

test('administrators can soft delete and restore a typing passage', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $passage = TypingPassage::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.typing-passages.destroy', $passage))
        ->assertRedirect();

    expect($passage->fresh()->trashed())->toBeTrue();

    $this->actingAs($admin)
        ->patch(route('admin.typing-passages.restore', $passage))
        ->assertRedirect();

    expect($passage->fresh()->trashed())->toBeFalse()
        ->and(AdminAuditLog::query()->where('action', 'typing-passage.deleted')->exists())->toBeTrue()
        ->and(AdminAuditLog::query()->where('action', 'typing-passage.restored')->exists())->toBeTrue();
});

test('typing passage validation enforces content and manual time bounds', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->post(route('admin.typing-passages.store'), [
            'language' => 'ar',
            'content' => 'قصير',
            'auto_calculate_time' => '0',
            'expected_seconds' => 9,
            'is_active' => '1',
        ])
        ->assertSessionHasErrors(['content', 'expected_seconds']);
});
