<?php

use App\Models\SmartBoard;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function smartBoardPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'title' => 'سبورة الصف الخامس',
        'state' => [
            'schemaVersion' => 1,
            'board' => [
                'background' => 'aurora',
                'aspectRatio' => 'auto',
                'drawing' => [
                    'tool' => 'pen',
                    'color' => '#6d28d9',
                    'width' => 4,
                    'strokes' => [],
                ],
            ],
            'widgets' => [[
                'id' => 'text-1', 'type' => 'text', 'x' => 10, 'y' => 12,
                'width' => 30, 'height' => 20, 'zIndex' => 1, 'locked' => false,
                'config' => ['content' => 'مرحبًا بالطلاب'],
            ]],
        ],
    ], $overrides);
}

test('a verified user may create load and update a smart board', function () {
    $user = User::factory()->create();
    $created = $this->actingAs($user)
        ->postJson(route('smart-boards.store'), smartBoardPayload())
        ->assertCreated()
        ->assertJsonPath('data.version', 1)
        ->json('data');

    $this->actingAs($user)->getJson(route('smart-boards.show', $created['id']))
        ->assertSuccessful()
        ->assertJsonPath('data.state.widgets.0.type', 'text')
        ->assertJsonPath('data.state.board.drawing.tool', 'pen');

    $this->actingAs($user)->patchJson(route('smart-boards.update', $created['id']), smartBoardPayload([
        'title' => 'سبورة العلوم', 'version' => 1,
    ]))->assertSuccessful()->assertJsonPath('data.title', 'سبورة العلوم')->assertJsonPath('data.version', 2);
});

test('stale updates return the latest board for conflict recovery', function () {
    $user = User::factory()->create();
    $board = SmartBoard::factory()->for($user)->create(['version' => 3]);

    $this->actingAs($user)
        ->patchJson(route('smart-boards.update', $board), smartBoardPayload(['version' => 2]))
        ->assertConflict()->assertJsonPath('conflict', true)
        ->assertJsonPath('data.version', 3)->assertJsonPath('data.state.schemaVersion', 1);
});

test('smart boards enforce ownership verification and state limits', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $board = SmartBoard::factory()->for($owner)->create();

    $this->actingAs($intruder)->getJson(route('smart-boards.show', $board))->assertForbidden();
    $this->actingAs(User::factory()->unverified()->create())
        ->postJson(route('smart-boards.store'), smartBoardPayload())->assertForbidden();

    $invalid = smartBoardPayload();
    $invalid['state']['widgets'] = array_fill(0, 41, $invalid['state']['widgets'][0]);
    $this->actingAs($owner)->postJson(route('smart-boards.store'), $invalid)
        ->assertUnprocessable()->assertJsonValidationErrors('state');
});

test('board images are private and limited to their owner', function () {
    Storage::fake('local');
    $owner = User::factory()->create();
    $board = SmartBoard::factory()->for($owner)->create();
    $assetId = $this->actingAs($owner)
        ->postJson(route('smart-boards.assets.store', $board), [
            'image' => UploadedFile::fake()->image('classroom.png', 800, 600),
        ])->assertCreated()->json('data.id');

    $this->actingAs($owner)->get(route('smart-board-assets.show', $assetId))->assertSuccessful();
    $this->actingAs(User::factory()->create())
        ->get(route('smart-board-assets.show', $assetId))->assertForbidden();
});
