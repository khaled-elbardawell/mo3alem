<?php

use App\Models\TypingPassage;
use App\SeoPage;
use App\Services\TypingPassageCalculator;
use App\TypingPassageLanguage;

test('the typing trainer is available to guests and uses the public tool shell', function () {
    $this->get(route('tools.typing-trainer'))
        ->assertSuccessful()
        ->assertViewIs('public.tools.typing-trainer')
        ->assertSee('data-tool-page-header', false)
        ->assertSee('data-typing-trainer', false)
        ->assertSee('data-typing-language="ar"', false)
        ->assertSee('data-typing-language="en"', false)
        ->assertSee('data-typing-trainer-config', false);
});

test('the random passage endpoint returns only active passages in the requested language', function () {
    $excluded = TypingPassage::factory()->arabic()->create();
    $expected = TypingPassage::factory()->arabic()->create([
        ...app(TypingPassageCalculator::class)->attributes('يتعلم الطالب تنظيم وقته من خلال خطة يومية واضحة تساعده على إنجاز واجباته ومراجعة دروسه بهدوء وثقة.'),
    ]);
    TypingPassage::factory()->arabic()->create([
        ...app(TypingPassageCalculator::class)->attributes('تظهر هذه الفقرة في قاعدة البيانات لكنها غير متاحة للمتدرب لأنها معطلة من لوحة إدارة المحتوى.'),
        'is_active' => false,
    ]);
    TypingPassage::factory()->create();

    $this->getJson(route('tools.typing-trainer.passages.random', [
        'language' => TypingPassageLanguage::Arabic->value,
        'exclude' => $excluded->id,
    ]))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $expected->id)
        ->assertJsonPath('data.language', 'ar')
        ->assertJsonPath('data.direction', 'rtl')
        ->assertJsonPath('data.content', $expected->content)
        ->assertJsonPath('data.word_count', $expected->word_count)
        ->assertJsonPath('data.expected_seconds', $expected->expected_seconds);
});

test('invalid languages are rejected and unavailable passages return not found', function () {
    TypingPassage::factory()->create(['is_active' => false]);

    $this->getJson(route('tools.typing-trainer.passages.random', ['language' => 'fr']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('language');

    $this->getJson(route('tools.typing-trainer.passages.random', ['language' => 'en']))
        ->assertNotFound();
});

test('deleted passages are never returned to the trainer', function () {
    $passage = TypingPassage::factory()->create();
    $passage->delete();

    $this->getJson(route('tools.typing-trainer.passages.random', ['language' => 'en']))
        ->assertNotFound();
});

test('the typing trainer is linked from the tools hub footer and sitemap', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('data-tool-card="typing-trainer"', false)
        ->assertSee(route('tools.typing-trainer'), false);

    $this->get(route('sitemap'))
        ->assertSuccessful()
        ->assertSee('<loc>'.route(SeoPage::TypingTrainer->routeName()).'</loc>', false);
});

test('typing passage calculations normalize content and use thirty standard words per minute', function () {
    $calculator = app(TypingPassageCalculator::class);
    $attributes = $calculator->attributes("  تدريب   واضح\nومفيد  ");

    expect($attributes)
        ->content->toBe('تدريب واضح ومفيد')
        ->word_count->toBe(3)
        ->expected_seconds->toBe(10)
        ->content_hash->toBe(hash('sha256', 'تدريب واضح ومفيد'));
});
