<?php

use App\Models\SmartBoardSession;

test('the smart board opens for guests with classroom widgets and full board drawing controls', function () {
    $response = $this->get(route('tools.smart-board'));
    $response->assertSuccessful()->assertSee('data-smart-board', false)
        ->assertSee('id="smartBoardStage"', false)->assertSee('id="presentationModeBtn"', false)
        ->assertSee('id="onboardingDialog"', false)->assertDontSee('data-view-endpoint', false)
        ->assertSee('id="smartBoardDrawingCanvas"', false)
        ->assertSee('id="smartBoardDrawingToolbar"', false)
        ->assertSee('id="smartBoardDock"', false)
        ->assertSee('id="smartBoardDockTools"', false)
        ->assertSee('id="toggleBoardDockBtn"', false)
        ->assertSee('id="smartBoardMoreBtn"', false)
        ->assertSee('id="smartBoardMoreMenu"', false)
        ->assertSee('aria-label="أدوات السبورة غير الظاهرة"', false)
        ->assertSee('تظهر هنا الأدوات غير الموجودة في الشريط حاليًا')
        ->assertSee('id="newBoardBtn"', false)
        ->assertSee('id="clearBoardBtn"', false)
        ->assertSee('id="boardBackgroundImageInput"', false)
        ->assertSee('id="boardBackgroundImagePreview" alt="معاينة صورة الخلفية" hidden', false)
        ->assertSee('id="boardBackgroundImageEmpty"', false)
        ->assertSee('id="boardBackgroundColor"', false)
        ->assertSee('id="boardBackgroundSize"', false)
        ->assertSee('id="boardBackgroundOverlay"', false)
        ->assertDontSee('id="boardShowGrid"', false)
        ->assertDontSee('شبكة إرشادية')
        ->assertDontSee('قالب بداية')
        ->assertDontSee('data-board-template', false)
        ->assertSee('value="image-school-supplies"', false)
        ->assertSee('value="image-learning-journey"', false)
        ->assertSee('value="image-creativity"', false)
        ->assertSee('value="image-science"', false)
        ->assertSee('images/smart-board/backgrounds/board-bg-img1.png', false)
        ->assertSee('images/smart-board/backgrounds/board-bg-img2.png', false)
        ->assertSee('images/smart-board/backgrounds/board-bg-img3.png', false)
        ->assertSee('images/smart-board/backgrounds/board-bg-img4.png', false)
        ->assertDontSee('images/smart-board/backgrounds/mountains.svg', false)
        ->assertSee('value="custom-image"', false)
        ->assertSee('aria-controls="smartBoardDockTools"', false)
        ->assertSee('aria-expanded="true"', false)
        ->assertSeeInOrder([
            'id="boardAppearanceBtn"',
            'data-board-mode="move"',
            'data-board-mode="draw"',
            'id="smartBoardMoreMenu"',
        ], false)
        ->assertDontSee('data-widget-type="drawing"', false);

    expect(substr_count($response->getContent(), 'id="boardAppearanceBtn"'))->toBe(1);

    foreach ([
        'text', 'clock', 'calendar', 'timer', 'stopwatch', 'traffic-light', 'scoreboard',
        'dice', 'randomizer', 'groups', 'image', 'embed', 'qr', 'poll',
    ] as $type) {
        $response->assertSee("data-add-widget data-widget-type=\"{$type}\"", false);
    }
    $response->assertDontSee('data-widget-type="work-mode"', false);
    expect($response->viewData('smartBoardConfig'))->toMatchArray(['authenticated' => false, 'verified' => false]);
});

test('the home page promotes the smart board', function () {
    $this->get(route('home'))->assertSuccessful()
        ->assertSee('data-tool-card="smart-board"', false)->assertSee(route('tools.smart-board'), false);
});

test('students may open the poll page without an account', function () {
    $session = SmartBoardSession::factory()->create();
    $this->get(route('smart-board.join', $session))->assertSuccessful()
        ->assertSee('data-smart-board-join', false)
        ->assertSee('data-response-endpoint=', false)
        ->assertDontSee('data-endpoint=', false)
        ->assertSee($session->public_code);
});
