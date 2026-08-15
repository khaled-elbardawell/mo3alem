<?php

use App\Models\Competition;
use App\Models\SavedWheel;
use App\Models\User;

test('guests may choose guest mode or receive a login prompt for save mode', function () {
    $this->get(route('tools.wheel'))
        ->assertSuccessful()
        ->assertSee('data-mode="guest"', false)
        ->assertSee('data-mode="save"', false)
        ->assertSee('id="guestSaveActions"', false)
        ->assertSee('تسجيل الدخول مطلوب')
        ->assertSee('id="wheelEditor"', false)
        ->assertDontSee('id="savedWheelsBrowser"', false)
        ->assertDontSee('id="createSavedWheelDialog"', false)
        ->assertDontSee('id="loadSavedWheelDialog"', false);
});

test('authenticated users start from competitions and may switch to saved name lists', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('tools.wheel'));

    $response
        ->assertSuccessful()
        ->assertDontSee('data-mode="guest"', false)
        ->assertSee('id="saveWorkspaceTabs"', false)
        ->assertSee('id="competitionsWorkspaceTab"', false)
        ->assertSee('data-save-workspace="competitions"', false)
        ->assertSee('id="listsWorkspaceTab"', false)
        ->assertSee('data-save-workspace="lists"', false)
        ->assertSeeInOrder(['مسابقاتي', 'قوائم الأسماء'])
        ->assertSeeInOrder([
            'id="competitionsWorkspaceTab"',
            'aria-pressed="true"',
            'id="listsWorkspaceTab"',
            'aria-pressed="false"',
        ], false)
        ->assertSee('id="competitionsBrowser"', false)
        ->assertSee('id="competitionsSearch"', false)
        ->assertSee('id="competitionsCards"', false)
        ->assertSee('id="competitionsLoader"', false)
        ->assertSee('id="createCompetitionBtn"', false)
        ->assertSee('id="createCompetitionDialog"', false)
        ->assertSee('id="savedWheelsBrowser"', false)
        ->assertSee('id="savedWheelsSearch"', false)
        ->assertSee('id="savedWheelsCards"', false)
        ->assertSee('id="savedWheelsLoader"', false)
        ->assertSee('id="createSavedWheelBtn"', false)
        ->assertSee('id="createSavedWheelDialog"', false)
        ->assertSee('id="backToSavedWheelsBtn"', false)
        ->assertSee('id="wheelEditor"', false)
        ->assertSee('id="savedWheelActiveState"', false)
        ->assertDontSee('id="loadSavedWheelDialog"', false)
        ->assertDontSee('id="loadSavedWheelBtn"', false)
        ->assertDontSee('id="guestSaveActions"', false);

    expect(strpos($response->getContent(), 'id="createSavedWheelBtn"'))
        ->toBeLessThan(strpos($response->getContent(), 'id="dataPage"'));

    $wheelConfig = $response->viewData('wheelConfig');
    $routes = $wheelConfig['routes'];

    expect($routes['index'])->toBe(route('saved-wheels.index'))
        ->and($routes['showBase'])->toBe(url('/saved-wheels'))
        ->and($routes['competitions']['index'])->toBe(route('competitions.index'))
        ->and($routes['competitions']['showBase'])->toBe(url('/competitions'))
        ->and($wheelConfig['limits'])->toBe([
            'savedWheels' => 6,
            'namesPerSavedWheel' => 2000,
        ])
        ->and($wheelConfig['usage']['savedWheels'])->toBe(0);
});

test('opening a competition exposes its participant snapshot and round history', function () {
    $user = User::factory()->create();
    $competition = Competition::factory()->for($user)->create([
        'names' => ['أحمد', 'سارة'],
        'names_count' => 2,
        'results' => [[
            'round' => 1,
            'name' => 'سارة',
            'date' => now()->toISOString(),
            'position' => 2,
        ]],
        'results_count' => 1,
    ]);

    $response = $this->actingAs($user)->get(route('tools.wheel', [
        'competition' => $competition->id,
    ]));

    $response
        ->assertSuccessful()
        ->assertSee('id="backToWorkspaceLabel"', false)
        ->assertSee('id="activeWorkspaceHint"', false);

    expect($response->viewData('wheelConfig')['competition'])
        ->toMatchArray([
            'id' => $competition->id,
            'names' => ['أحمد', 'سارة'],
            'results_count' => 1,
        ])
        ->and($response->viewData('wheelConfig')['competition']['results'][0]['round'])
        ->toBe(1);
});

test('opening a saved list exposes only names and not saved results', function () {
    $user = User::factory()->create();
    $wheel = SavedWheel::factory()->for($user)->create([
        'names' => ['أحمد', 'سارة'],
        'names_count' => 2,
        'results' => [['name' => 'سارة', 'date' => now()->toISOString()]],
    ]);

    $response = $this->actingAs($user)->get(route('tools.wheel', ['wheel' => $wheel->id]));

    $response
        ->assertSuccessful()
        ->assertSee('id="backToSavedWheelsBtn"', false);

    $response->assertSeeInOrder([
        'id="competitionsWorkspaceTab"',
        'aria-pressed="false"',
        'id="listsWorkspaceTab"',
        'aria-pressed="true"',
    ], false);

    expect($response->viewData('wheelConfig')['savedWheel'])
        ->toMatchArray([
            'id' => $wheel->id,
            'names' => ['أحمد', 'سارة'],
        ])
        ->not->toHaveKey('results');
});

test('the wheel page includes an accessible progress loader for file imports', function () {
    $response = $this->get(route('tools.wheel'));

    $response
        ->assertSuccessful()
        ->assertSeeInOrder([
            'id="dataPage"',
            'id="addNameBtn"',
            'id="importTrigger"',
            'id="virtualList"',
        ], false)
        ->assertSee('الأسماء (0)')
        ->assertDontSee('id="emptyAddNameBtn"', false)
        ->assertDontSee('id="emptyImportNamesBtn"', false)
        ->assertSee('id="importLoader"', false)
        ->assertSee('id="importProgressBar"', false)
        ->assertSee('aria-live="assertive"', false);

    $script = file_get_contents(resource_path('js/app.js'));

    expect($script)
        ->toContain('importTrigger?.addEventListener("click", () => importInput.click())')
        ->not->toContain('emptyAddNameBtn')
        ->not->toContain('emptyImportNamesBtn');
});

test('wheel controls use move terminology', function () {
    $template = file_get_contents(resource_path('views/public/tools/wheel.blade.php'));
    $script = file_get_contents(resource_path('js/app.js'));
    $moveWheelLabel = "\u{062D}\u{0631}\u{0643} \u{0627}\u{0644}\u{0639}\u{062C}\u{0644}\u{0629}";
    $spinWheelLabel = "\u{0644}\u{0641} \u{0627}\u{0644}\u{0639}\u{062C}\u{0644}\u{0629}";
    $automaticSpinLabel = "\u{0644}\u{0641} \u{062A}\u{0644}\u{0642}\u{0627}\u{0626}\u{064A}";
    $spinningLabel = "\u{062C}\u{0627}\u{0631}\u{064A} \u{0627}\u{0644}\u{0644}\u{0641}";

    expect($template)
        ->toContain($moveWheelLabel)
        ->not->toContain($spinWheelLabel)
        ->not->toContain($automaticSpinLabel);

    expect($script)
        ->toContain($moveWheelLabel)
        ->not->toContain($spinWheelLabel)
        ->not->toContain($spinningLabel);
});

test('automatic wheel movement delay is configurable', function () {
    $response = $this->get(route('tools.wheel'));

    $response
        ->assertSuccessful()
        ->assertSeeInOrder([
            'id="autoSpin"',
            'id="autoSpinDelay"',
            'type="number"',
            'min="1"',
            'max="60"',
            'value="5"',
        ], false);

    $script = file_get_contents(resource_path('js/app.js'));

    expect($script)
        ->toContain('getAutoSpinDelayMilliseconds()')
        ->toContain('return delaySeconds * 1000;')
        ->toContain('if (autoSpin.checked) setAutoSpin(true);')
        ->not->toContain('}, 5000);');
});

test('celebration sound uses the public asset path', function () {
    $script = file_get_contents(resource_path('js/app.js'));

    expect(file_exists(public_path('assets/voice.m4a')))->toBeTrue()
        ->and($script)
        ->toContain('new Audio("/assets/voice.m4a")')
        ->not->toContain('new Audio("./assets/voice.m4a")');
});

test('celebration asks whether to remove or keep the winner', function () {
    $response = $this->get(route('tools.wheel'));

    $response
        ->assertSuccessful()
        ->assertSee('id="celebrationPrompt"', false)
        ->assertSee('id="removeCelebrationWinnerBtn"', false)
        ->assertSee('حذف الفائز')
        ->assertSee('id="keepCelebrationWinnerBtn"', false)
        ->assertSee('إبقاء الفائز');

    $script = file_get_contents(resource_path('js/app.js'));
    $spinFunction = substr(
        $script,
        strpos($script, 'function spinWheel()'),
        strpos($script, 'function addWinner(') - strpos($script, 'function spinWheel()'),
    );
    $removeWinnerDecisionFunction = substr(
        $script,
        strpos($script, 'function removeCelebrationWinner()'),
        strpos($script, 'function keepCelebrationWinner()') - strpos($script, 'function removeCelebrationWinner()'),
    );
    $keepWinnerDecisionFunction = substr(
        $script,
        strpos($script, 'function keepCelebrationWinner()'),
        strpos($script, 'function launchConfetti()') - strpos($script, 'function keepCelebrationWinner()'),
    );

    expect($script)
        ->toContain('showCelebration(winner, selectedIndex);')
        ->not->toContain('removeWinnerFromNames(selectedIndex, winner);')
        ->toContain('function removeCelebrationWinner()')
        ->toContain('addWinner(winnerDecision.name, winnerDecision.nameNumber);')
        ->toContain('function keepCelebrationWinner()')
        ->not->toContain('celebrationTimer = setTimeout')
        ->and($spinFunction)
        ->not->toContain('addWinner(')
        ->and($removeWinnerDecisionFunction)
        ->toContain('addWinner(winnerDecision.name, winnerDecision.nameNumber);')
        ->toContain('removeWinnerFromNames(winnerDecision.selectedIndex, winnerDecision.name);')
        ->and($keepWinnerDecisionFunction)
        ->not->toContain('addWinner(')
        ->not->toContain('removeWinnerFromNames(');
});

test('the client enforces the agreed list and autosave safeguards', function () {
    $script = file_get_contents(resource_path('js/app.js'));

    expect($script)
        ->toContain('const maximumSavedWheels = Number(wheelConfig.limits?.savedWheels) || 6;')
        ->toContain('const maximumNames = Number(wheelConfig.limits?.namesPerSavedWheel) || 2000;')
        ->toContain('if (savedWheelLimitReached())')
        ->toContain('window.setTimeout(() => saveCurrentWheel(), 2000)')
        ->toContain('let saveInFlightPromise = null;')
        ->toContain('getSavedListSnapshot() !== lastSavedSnapshot')
        ->toContain('savedWheelsSearchTimer = window.setTimeout(() => loadSavedWheels({ reset: true }), 300)')
        ->toContain('competitionsSearchTimer = window.setTimeout(() => loadCompetitions({ reset: true }), 300)')
        ->toContain('function normalizeNames(inputNames)')
        ->toContain('.filter((name) => typeof name === "string")')
        ->toContain('.map((name) => name.trim().slice(0, 120))')
        ->toContain('names: normalizeNames(names)')
        ->toContain('...(isCompetition ? { results: serializeResults() } : {})');
});

test('save workspace tabs preserve autosave before changing sections', function () {
    $script = file_get_contents(resource_path('js/app.js'));

    expect($script)
        ->toContain('const saveWorkspaceTabs = document.querySelectorAll("[data-save-workspace]");')
        ->toContain('async function openSaveWorkspace(workspace = currentCompetition ? "competitions" : "lists")')
        ->toContain('const saved = await flushAutosave();')
        ->toContain('button.addEventListener("click", () => openSaveWorkspace(button.dataset.saveWorkspace));')
        ->not->toContain('manageSavedWheelsBtn')
        ->not->toContain('backToCompetitionsFromListsBtn');
});

test('pressing enter confirms name list and competition inputs', function () {
    $script = file_get_contents(resource_path('js/app.js'));

    expect($script)
        ->toContain('nameInput.addEventListener("keydown", (event) => {')
        ->toContain('if (!confirmAddName.disabled) confirmAddName.click();')
        ->toContain('savedWheelTitle?.addEventListener("keydown", (event) => {')
        ->toContain('if (!confirmCreateSavedWheelBtn?.disabled) confirmCreateSavedWheelBtn?.click();')
        ->toContain('[competitionTitle, competitionNewListTitle].filter(Boolean).forEach((input) => {')
        ->toContain('if (!confirmCreateCompetitionBtn?.disabled) confirmCreateCompetitionBtn?.click();')
        ->toContain('event.isComposing || event.keyCode === 229');
});

test('the competition flow keeps results grouped by round and lists independent', function () {
    $script = file_get_contents(resource_path('js/app.js'));

    expect($script)
        ->toContain('round: Number.isFinite(winner.round)')
        ->toContain('اللفة ${formatNumber(winner.round || winners.length - index)}')
        ->toContain('const canRunCompetition = !wheelConfig.authenticated || Boolean(currentCompetition);')
        ->toContain('const isSavedList = Boolean(currentSavedWheel);')
        ->toContain('activeWorkspaceHint.hidden = isSavedList;')
        ->toContain('activeSavedWheelTitle.classList.toggle(className, isSavedList);');
});

test('shared styles use the hand cursor for interactive controls', function () {
    $styles = file_get_contents(resource_path('css/app.css'));

    expect($styles)
        ->toContain('a[href]:not([aria-disabled="true"])')
        ->toContain('button:not(:disabled)')
        ->toContain('[role="button"]:not([aria-disabled="true"])')
        ->toContain('cursor: pointer;')
        ->toContain('cursor: not-allowed;');
});
