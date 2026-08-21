<?php

namespace App\Models;

use Database\Factories\SeoSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'page_key',
    'site_name',
    'title',
    'description',
    'keywords',
    'canonical_url',
    'allow_indexing',
    'allow_following',
    'og_title',
    'og_description',
    'og_image_path',
    'og_image_alt',
    'twitter_card',
    'include_in_sitemap',
    'sitemap_change_frequency',
    'sitemap_priority',
])]
class SeoSetting extends Model
{
    /** @use HasFactory<SeoSettingFactory> */
    use HasFactory;

    protected $attributes = [
        'site_name' => 'معلم',
        'title' => 'معلم | أدوات ذكية لكل معلم',
        'description' => 'منصة أدوات تعليمية عربية تساعد المعلم على إنشاء الأنشطة ورموز QR والشهادات بسهولة واحترافية.',
        'keywords' => 'أدوات المعلم, عجلة الأسماء, إنشاء QR, إنشاء شهادات, معلم',
        'allow_indexing' => true,
        'allow_following' => true,
        'twitter_card' => 'summary_large_image',
        'include_in_sitemap' => true,
        'sitemap_change_frequency' => 'weekly',
        'sitemap_priority' => 0.9,
    ];

    protected function casts(): array
    {
        return [
            'allow_indexing' => 'boolean',
            'allow_following' => 'boolean',
            'include_in_sitemap' => 'boolean',
            'sitemap_priority' => 'float',
        ];
    }
}
