<?php

test('each public tool starts with the shared compact page header', function (string $route, string $title, string $current, string $icon) {
    $this->get(route($route))
        ->assertSuccessful()
        ->assertSee('data-tool-page-header', false)
        ->assertSee('aria-label="مسار التنقل"', false)
        ->assertSee('aria-current="page"', false)
        ->assertSee($title)
        ->assertSee($current)
        ->assertSee($icon, false);
})->with([
    'wheel' => ['tools.wheel', 'أداة عجلة الأسماء العشوائية', 'عجلة الأسماء', 'fa-dharmachakra'],
    'qr' => ['tools.qr', 'أداة إنشاء رمز QR', 'إنشاء رمز QR', 'fa-qrcode'],
    'certificates' => ['tools.certificates', 'أداة إنشاء الشهادات', 'إنشاء الشهادات', 'fa-award'],
]);
