<?php

use App\Models\SmartBoardSession;

function pollSessionPayload(): array
{
    return [
        'poll_key' => 'poll-ready',
        'poll' => [
            'question' => 'هل أنتم مستعدون؟',
            'options' => [
                ['key' => 'yes', 'label' => 'نعم'],
                ['key' => 'wait', 'label' => 'ليس بعد'],
            ],
        ],
    ];
}

test('a guest may create a short lived anonymous poll session', function () {
    $created = $this->postJson(route('smart-board-sessions.store'), pollSessionPayload())
        ->assertCreated()->assertJsonPath('data.poll.question', 'هل أنتم مستعدون؟')
        ->assertJsonStructure(['data' => ['code', 'owner_token', 'join_url', 'expires_at']])
        ->json('data');

    expect($created['code'])->toHaveLength(6)->and($created['owner_token'])->not->toBeEmpty();
    $this->getJson(route('smart-board-sessions.show', $created['code']))
        ->assertSuccessful()->assertJsonMissingPath('data.owner_token');
});

test('only the owner token may change a guest session', function () {
    $created = $this->postJson(route('smart-board-sessions.store'), pollSessionPayload())->json('data');
    $route = route('smart-board-sessions.update', $created['code']);
    $this->patchJson($route, ['status' => 'closed'])->assertForbidden();
    $this->withHeader('X-Smart-Board-Token', $created['owner_token'])
        ->patchJson($route, ['status' => 'closed'])
        ->assertSuccessful()->assertJsonPath('data.status', 'closed');
});

test('students vote anonymously once and may change their response', function () {
    $created = $this->postJson(route('smart-board-sessions.store'), pollSessionPayload())->json('data');
    $route = route('smart-board-sessions.responses.store', $created['code']);
    $response = ['poll_key' => 'poll-ready', 'option_key' => 'yes', 'participant_token' => 'participant-token-that-is-long-enough'];
    $this->postJson($route, $response)->assertSuccessful()->assertJsonPath('data.poll.options.0.count', 1);
    $response['option_key'] = 'wait';
    $this->postJson($route, $response)->assertSuccessful()
        ->assertJsonPath('data.poll.options.0.count', 0)
        ->assertJsonPath('data.poll.options.1.count', 1);
});

test('expired sessions reject new responses', function () {
    $session = SmartBoardSession::factory()->create(['expires_at' => now()->subMinute()]);
    $this->postJson(route('smart-board-sessions.responses.store', $session), [
        'poll_key' => $session->poll_key, 'option_key' => 'yes',
        'participant_token' => 'participant-token-that-is-long-enough',
    ])->assertUnprocessable()->assertJsonValidationErrors('session');
});
