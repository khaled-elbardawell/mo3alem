<?php

use App\Models\ShortLink;
use App\Models\ShortLinkDailyStat;
use App\Models\User;
use App\Services\VisitorIdentity;
use Illuminate\Support\Str;

function shortLinkPayload(array $overrides = []): array
{
    return [
        'title' => 'نشاط العلوم',
        'destination_url' => 'https://example.com/lessons/science',
        'custom_alias' => 'science-class',
        'is_active' => '1',
        'expires_at' => now()->addWeek()->format('Y-m-d\TH:i'),
        ...$overrides,
    ];
}

test('a verified user can create a custom encrypted short link', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('short-links.store'), shortLinkPayload(['custom_alias' => '  SCIENCE-Class  ']))
        ->assertRedirect();

    $shortLink = ShortLink::query()->firstOrFail();

    expect($shortLink->user->is($user))->toBeTrue()
        ->and($shortLink->slug)->toBe('science-class')
        ->and($shortLink->title)->toBe('نشاط العلوم')
        ->and($shortLink->destination_url)->toBe('https://example.com/lessons/science')
        ->and($shortLink->getRawOriginal('destination_url'))->not->toContain('example.com')
        ->and($shortLink->version)->toBe(1);
});

test('a random seven character slug and destination host title are generated when omitted', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('short-links.store'), shortLinkPayload([
            'title' => '',
            'custom_alias' => '',
            'expires_at' => '',
        ]))
        ->assertSessionHasNoErrors();

    $shortLink = ShortLink::query()->firstOrFail();

    expect($shortLink->slug)->toHaveLength(7)
        ->toMatch('/^[a-z0-9]{7}$/')
        ->and($shortLink->title)->toBe('example.com')
        ->and($shortLink->expires_at)->toBeNull();
});

test('the short url tool can create a link without optional fields using json', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson(route('short-links.store'), [
            'destination_url' => 'https://example.com/a/very/long/path',
        ]);

    $shortLink = ShortLink::query()->firstOrFail();

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'تم إنشاء الرابط المختصر بنجاح.')
        ->assertJsonPath('data.slug', $shortLink->slug)
        ->assertJsonPath('data.short_url', route('short-links.redirect', $shortLink->slug))
        ->assertJsonPath('data.qr_url', route('short-links.qr', $shortLink))
        ->assertJsonPath('data.update_slug_url', route('short-links.slug.update', $shortLink));

    expect($shortLink->is_active)->toBeTrue()
        ->and($shortLink->expires_at)->toBeNull();
});

test('custom aliases are validated globally and remain reserved after deletion', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $shortLink = ShortLink::factory()->for($owner)->create(['slug' => 'reserved-link']);
    $shortLink->delete();

    $this->actingAs($otherUser)
        ->post(route('short-links.store'), shortLinkPayload(['custom_alias' => 'reserved-link']))
        ->assertSessionHasErrors('custom_alias');

    $this->actingAs($otherUser)
        ->post(route('short-links.store'), shortLinkPayload(['custom_alias' => 'bad_alias']))
        ->assertSessionHasErrors('custom_alias');
});

test('only http destinations are accepted and local short link chains are rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('short-links.store'), shortLinkPayload([
            'destination_url' => 'javascript:alert(1)',
        ]))
        ->assertSessionHasErrors('destination_url');

    $this->actingAs($user)
        ->post(route('short-links.store'), shortLinkPayload([
            'destination_url' => route('short-links.redirect', 'another-link'),
        ]))
        ->assertSessionHasErrors('destination_url');
});

test('creation requires a verified account and respects the configurable saved limit', function () {
    $unverifiedUser = User::factory()->unverified()->create();

    $this->actingAs($unverifiedUser)
        ->post(route('short-links.store'), shortLinkPayload())
        ->assertRedirect(route('verification.notice'));

    config(['resource_limits.short_links' => 2]);
    $user = User::factory()->create();
    ShortLink::factory()->count(2)->for($user)->create();

    $this->actingAs($user)
        ->post(route('short-links.store'), shortLinkPayload(['custom_alias' => 'third-link']))
        ->assertSessionHasErrors('destination_url');
});

test('an owner can update the link management fields without changing its slug', function () {
    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->create([
        'slug' => 'fixed-link',
        'version' => 1,
    ]);

    $this->actingAs($user)
        ->patch(route('short-links.update', $shortLink), [
            'title' => 'العنوان المعدّل',
            'destination_url' => 'https://example.org/new-destination',
            'custom_alias' => 'changed-link',
            'is_active' => '0',
            'expires_at' => now()->addMonth()->format('Y-m-d\TH:i'),
            'version' => 1,
        ])
        ->assertRedirect(route('user.short-links.show', $shortLink));

    expect($shortLink->refresh()->slug)->toBe('fixed-link')
        ->and($shortLink->title)->toBe('العنوان المعدّل')
        ->and($shortLink->destination_url)->toBe('https://example.org/new-destination')
        ->and($shortLink->is_active)->toBeFalse()
        ->and($shortLink->version)->toBe(2);
});

test('an owner can customize the generated slug and receives the updated result', function () {
    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->create([
        'slug' => 'random1',
        'version' => 1,
    ]);

    $this->actingAs($user)
        ->patchJson(route('short-links.slug.update', $shortLink), [
            'slug' => '  My-Class-Link  ',
            'version' => 1,
        ])
        ->assertSuccessful()
        ->assertJsonPath('message', 'تم حفظ المقطع الجديد بنجاح.')
        ->assertJsonPath('data.slug', 'my-class-link')
        ->assertJsonPath('data.short_url', route('short-links.redirect', 'my-class-link'))
        ->assertJsonPath('data.version', 2);

    expect($shortLink->refresh()->slug)->toBe('my-class-link')
        ->and($shortLink->version)->toBe(2);
});

test('customizing a slug reports reserved aliases and stale versions', function () {
    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->create(['version' => 3]);
    $reserved = ShortLink::factory()->create(['slug' => 'reserved-link']);
    $reserved->delete();

    $this->actingAs($user)
        ->patchJson(route('short-links.slug.update', $shortLink), [
            'slug' => 'reserved-link',
            'version' => 3,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('slug');

    $this->actingAs($user)
        ->patchJson(route('short-links.slug.update', $shortLink), [
            'slug' => 'fresh-link',
            'version' => 2,
        ])
        ->assertConflict()
        ->assertJsonPath('errors.version.0', 'تم تعديل الرابط من جلسة أخرى. حدّث الصفحة ثم أعد المحاولة.');
});

test('another user cannot customize a short link slug', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $shortLink = ShortLink::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->patchJson(route('short-links.slug.update', $shortLink), [
            'slug' => 'stolen-link',
            'version' => $shortLink->version,
        ])
        ->assertForbidden();
});

test('stale updates are rejected and other users cannot manage a link', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $shortLink = ShortLink::factory()->for($owner)->create(['version' => 3]);

    $this->actingAs($owner)
        ->patch(route('short-links.update', $shortLink), [
            ...shortLinkPayload(['custom_alias' => null]),
            'version' => 2,
        ])
        ->assertSessionHasErrors('version');

    $this->actingAs($intruder)
        ->get(route('user.short-links.show', $shortLink))
        ->assertForbidden();

    $this->actingAs($intruder)
        ->delete(route('short-links.destroy', $shortLink))
        ->assertForbidden();

    $this->actingAs($intruder)
        ->get(route('short-links.qr', $shortLink))
        ->assertForbidden();
});

test('valid redirects record total and daily unique visits without storing raw identities', function () {
    $this->withoutDefer();
    $shortLink = ShortLink::factory()->create([
        'destination_url' => 'https://example.com/target',
        'slug' => 'tracked-link',
    ]);
    $visitorId = (string) Str::uuid();

    $this->withCookie(VisitorIdentity::COOKIE_NAME, $visitorId)
        ->get(route('short-links.redirect', $shortLink->slug))
        ->assertRedirect('https://example.com/target')
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

    $this->withCookie(VisitorIdentity::COOKIE_NAME, $visitorId)
        ->get(route('short-links.redirect', $shortLink->slug))
        ->assertRedirect('https://example.com/target');

    $dailyStat = ShortLinkDailyStat::query()->whereBelongsTo($shortLink)->firstOrFail();

    expect($shortLink->refresh()->visit_count)->toBe(2)
        ->and($shortLink->unique_visit_count)->toBe(1)
        ->and($shortLink->last_visited_at)->not->toBeNull()
        ->and($dailyStat->visits)->toBe(2)
        ->and($dailyStat->unique_visits)->toBe(1);
});

test('paused expired and deleted links do not record visits', function () {
    $this->withoutDefer();
    $paused = ShortLink::factory()->create(['slug' => 'paused-link', 'is_active' => false]);
    $expired = ShortLink::factory()->create(['slug' => 'expired-link', 'expires_at' => now()->subMinute()]);
    $deleted = ShortLink::factory()->create(['slug' => 'deleted-link']);
    $deleted->delete();

    $this->get(route('short-links.redirect', $paused->slug))
        ->assertNotFound()
        ->assertSee('الرابط متوقف مؤقتًا');
    $this->get(route('short-links.redirect', $expired->slug))
        ->assertGone()
        ->assertSee('انتهت صلاحية هذا الرابط');
    $this->get(route('short-links.redirect', $deleted->slug))
        ->assertGone();

    expect(ShortLinkDailyStat::query()->count())->toBe(0);
});

test('an owner can download an svg qr for the short url', function () {
    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->create(['slug' => 'qr-link']);

    $this->actingAs($user)
        ->get(route('short-links.qr', $shortLink))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'image/svg+xml; charset=UTF-8')
        ->assertSee('<svg', false);
});

test('deleting a link soft deletes it and its public slug returns gone', function () {
    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->create(['slug' => 'remove-link']);

    $this->actingAs($user)
        ->delete(route('short-links.destroy', $shortLink))
        ->assertRedirect(route('dashboard', ['section' => 'links']));

    $this->assertSoftDeleted($shortLink);
    $this->get(route('short-links.redirect', $shortLink->slug))->assertGone();
});
