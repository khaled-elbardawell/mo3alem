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
        'site_name' => 'نرد',
        'title' => 'نرد | عجلة الحظ',
        'description' => 'عجلة اختيار أسماء عربية مجانية وسهلة الاستخدام مع حفظ القوائم والنتائج.',
        'keywords' => 'عجلة الحظ, اختيار أسماء, عجلة أسماء, نرد',
        'allow_indexing' => true,
        'twitter_card' => 'summary_large_image',
    ];

    protected function casts(): array
    {
        return ['allow_indexing' => 'boolean'];
    }
}
