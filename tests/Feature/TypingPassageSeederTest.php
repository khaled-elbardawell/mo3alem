<?php

use App\Models\TypingPassage;
use App\Services\TypingPassageCalculator;
use Database\Seeders\TypingPassageSeeder;

test('the typing passage seeder creates a unique bilingual library and is idempotent', function () {
    $this->seed(TypingPassageSeeder::class);

    expect(TypingPassage::query()->where('language', 'ar')->count())->toBe(1200)
        ->and(TypingPassage::query()->where('language', 'en')->count())->toBe(1200)
        ->and(TypingPassage::query()->distinct('content_hash')->count('content_hash'))->toBe(2400)
        ->and(TypingPassage::query()->whereNotBetween('word_count', [20, 45])->exists())->toBeFalse();

    $calculator = app(TypingPassageCalculator::class);
    TypingPassage::query()->select(['content', 'expected_seconds'])->each(function (TypingPassage $passage) use ($calculator): void {
        expect($passage->expected_seconds)->toBe($calculator->expectedSeconds($passage->content));
    });

    $this->seed(TypingPassageSeeder::class);

    expect(TypingPassage::query()->count())->toBe(2400);
});
