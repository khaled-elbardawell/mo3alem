<?php

namespace App;

enum AdPlacement: string
{
    case Top = 'top';
    case Side = 'side';
    case Bottom = 'bottom';

    public function label(): string
    {
        return match ($this) {
            self::Top => 'علوي',
            self::Side => 'جانبي',
            self::Bottom => 'سفلي',
        };
    }

    public function recommendedImageDimensions(): string
    {
        return match ($this) {
            self::Top, self::Bottom => '1200 × 300 بكسل',
            self::Side => '300 × 600 بكسل',
        };
    }
}
