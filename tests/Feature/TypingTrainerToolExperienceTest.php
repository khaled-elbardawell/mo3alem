<?php

test('typing trainer opens for guests with Arabic and English learning controls', function () {
    $this->get(route('tools.typing-trainer'))
        ->assertSuccessful()
        ->assertSee('data-typing-trainer', false)
        ->assertSee('data-typing-language="ar"', false)
        ->assertSee('data-typing-language="en"', false)
        ->assertSee('data-typing-keyboard', false)
        ->assertSee('data-typing-keyboard-toggle', false)
        ->assertSee('data-typing-keyboard-panel', false)
        ->assertSee('data-typing-sound-toggle', false)
        ->assertSee('data-typing-sound-icon', false)
        ->assertSee('data-typing-sound-label', false)
        ->assertSee('كتم الصوت')
        ->assertSee('aria-expanded="false"', false)
        ->assertSee('id="typingKeyboardPanel" data-typing-keyboard-panel aria-label="لوحة المفاتيح التفاعلية" hidden', false)
        ->assertSee('إظهار لوحة المفاتيح')
        ->assertSee('data-typing-text', false)
        ->assertSee('data-typing-result-dialog', false)
        ->assertDontSee('data-typing-desktop-hint', false)
        ->assertDontSee('ابدأ الكتابة مباشرة من لوحة المفاتيح')
        ->assertDontSee('أفضل تجربة تكون بلوحة مفاتيح فعلية')
        ->assertSee('data-typing-intro', false)
        ->assertSee('data-typing-practice hidden', false)
        ->assertSee('data-typing-stat-card="cpm"', false)
        ->assertSee('data-typing-stat-card="errors"', false)
        ->assertSee('images/typing-trainer/home-row-hands.png', false)
        ->assertSee('استعد للدرس')
        ->assertSee('وضعية اليد الصحيحة')
        ->assertSee('ابدأ التدريب')
        ->assertSee('data-typing-start', false)
        ->assertSee('مدرب الطباعة بالعربية والإنجليزية')
        ->assertDontSee('يُحفظ تقدمك على هذا الجهاز فقط');
});

test('home page and footer promote the typing trainer', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('data-tool-card="typing-trainer"', false)
        ->assertSee(route('tools.typing-trainer'), false)
        ->assertSee('مدرب الطباعة');
});
