<?php

namespace App;

enum TypingPassageLanguage: string
{
    case Arabic = 'ar';
    case English = 'en';

    public function label(): string
    {
        return match ($this) {
            self::Arabic => 'العربية',
            self::English => 'الإنجليزية',
        };
    }

    public function direction(): string
    {
        return match ($this) {
            self::Arabic => 'rtl',
            self::English => 'ltr',
        };
    }
}
