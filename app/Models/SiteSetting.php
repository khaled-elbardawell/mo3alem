<?php

namespace App\Models;

use Database\Factories\SiteSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'footer_links'])]
class SiteSetting extends Model
{
    /** @use HasFactory<SiteSettingFactory> */
    use HasFactory;

    public const SITE_KEY = 'site';

    protected $attributes = [
        'key' => self::SITE_KEY,
    ];

    protected function casts(): array
    {
        return [
            'footer_links' => 'array',
        ];
    }
}
