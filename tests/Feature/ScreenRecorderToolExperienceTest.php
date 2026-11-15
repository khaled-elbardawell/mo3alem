<?php

use App\Models\DailyMetric;

test('the screen recorder opens for guests with local-only recording and editing controls', function () {
    $response = $this->get(route('tools.screen-recorder'));

    $response->assertSuccessful()
        ->assertViewIs('public.tools.screen-recorder')
        ->assertSee('data-screen-recorder', false)
        ->assertSee('data-privacy-banner', false)
        ->assertSee('data-record-microphone', false)
        ->assertSee('data-record-camera', false)
        ->assertSee('data-recording-panel', false)
        ->assertSee('data-editor-panel', false)
        ->assertSee('data-editor-workspace', false)
        ->assertSee('data-preview-workspace', false)
        ->assertSee('data-settings-panel', false)
        ->assertDontSee('data-overlay-settings-slot', false)
        ->assertDontSee('data-clips-panel', false)
        ->assertDontSee('data-clip-list', false)
        ->assertDontSee('data-clip-item-template', false)
        ->assertDontSee('data-audio-waveform', false)
        ->assertSee('data-editor-actionbar', false)
        ->assertSee('data-selection-start', false)
        ->assertSee('data-selection-end', false)
        ->assertDontSee('data-annotation-toolbar', false)
        ->assertSee('data-add-overlay="text"', false)
        ->assertSee('data-add-overlay="arrow"', false)
        ->assertSee('data-add-overlay="blur"', false)
        ->assertSee('data-overlay-create-dialog', false)
        ->assertSee('data-overlay-create-form', false)
        ->assertSee('data-overlay-create-mode', false)
        ->assertSee('data-overlay-create-text', false)
        ->assertSee('data-overlay-create-start', false)
        ->assertSee('data-overlay-create-end', false)
        ->assertSee('data-overlay-create-direction', false)
        ->assertSee('data-overlay-create-blur', false)
        ->assertSee('data-overlay-section', false)
        ->assertSee('data-timeline-section', false)
        ->assertDontSee('>الفيديو</span>', false)
        ->assertSee('data-overlay-add-controls', false)
        ->assertSee('data-overlay-list', false)
        ->assertSee('data-timeline-ruler', false)
        ->assertSee('data-timeline-grid', false)
        ->assertSee('data-timeline-viewport', false)
        ->assertSee('data-timeline-zoom', false)
        ->assertSee('data-timeline-current-time', false)
        ->assertDontSee('data-text-overlay-track', false)
        ->assertDontSee('data-visual-overlay-track', false)
        ->assertSee('data-overlay-item-template', false)
        ->assertSee('data-overlay-edit', false)
        ->assertSee('data-overlay-delete', false)
        ->assertSee('data-overlay-create-submit', false)
        ->assertDontSee('data-overlay-settings', false)
        ->assertDontSee('data-remove-overlay', false)
        ->assertSee('data-edit-action="apply-trim"', false)
        ->assertSee('تطبيق القص')
        ->assertSee('يحذف ما قبل البداية وما بعد النهاية')
        ->assertSee('data-export-dialog', false)
        ->assertSee('data-start-export', false)
        ->assertSee('الخط الزمني')
        ->assertSee('إعدادات الفيديو')
        ->assertSee('الإضافات')
        ->assertDontSee('النصوص')
        ->assertDontSee('العناصر')
        ->assertDontSee('المقاطع')
        ->assertSee('مشاريعي المحلية')
        ->assertSee('الفيديو والصوت لا يُرفعان إلى السيرفر.')
        ->assertDontSee('data-upload-endpoint', false);

    expect(substr_count($response->getContent(), 'data-export-video'))->toBe(1);

    expect($response->viewData('screenRecorderConfig'))->toMatchArray([
        'metricsEndpoint' => route('activity-metrics.store'),
        'maxDurationSeconds' => 3600,
        'storageReserveBytes' => 268_435_456,
    ]);
});

test('the screen recorder has seo metadata and appears across the public hub', function () {
    $this->get(route('tools.screen-recorder'))
        ->assertSuccessful()
        ->assertSee('تسجيل الشاشة وتحرير الفيديو محليًا | معلم')
        ->assertSee('حفظه محليًا دون رفع التسجيل إلى السيرفر');

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('data-tool-card="screen-recorder"', false)
        ->assertSee('data-tool-card="screen-recorder" data-reveal data-lift-card', false)
        ->assertSee(route('tools.screen-recorder'), false);

    $this->get(route('sitemap'))
        ->assertSuccessful()
        ->assertSee('<loc>'.route('tools.screen-recorder').'</loc>', false);
});

test('screen recording activity accepts only the supported anonymous event names', function () {
    $events = [
        'screen_recording_started' => 'screen_recordings_started',
        'screen_recording_exported' => 'screen_recordings_exported',
        'screen_recording_failed' => 'screen_recordings_failed',
    ];

    foreach ($events as $event => $column) {
        $this->postJson(route('activity-metrics.store'), ['event' => $event])->assertNoContent();

        expect((int) DailyMetric::query()->whereDate('date', today())->value($column))->toBe(1);
    }

    $this->postJson(route('activity-metrics.store'), ['event' => 'screen_recording_saved_to_cloud'])
        ->assertUnprocessable();

    expect(DailyMetric::query()->whereDate('date', today())->count())->toBe(1);
});
