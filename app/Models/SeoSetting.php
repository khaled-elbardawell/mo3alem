<?php

namespace App\Models;

use Database\Factories\SeoSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['site_name', 'title', 'description', 'keywords', 'canonical_url', 'allow_indexing', 'og_image_path', 'twitter_card'])]
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
        'twitter_card' => 'summary_large_image',
    ];

    protected function casts(): array
    {
        return ['allow_indexing' => 'boolean'];
    }
}
