<?php

namespace App\Models;

use App\TypingPassageLanguage;
use Database\Factories\TypingPassageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['seed_key', 'language', 'content', 'content_hash', 'word_count', 'expected_seconds', 'is_active'])]
class TypingPassage extends Model
{
    /** @use HasFactory<TypingPassageFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'is_active' => true,
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected function casts(): array
    {
        return [
            'language' => TypingPassageLanguage::class,
            'word_count' => 'integer',
            'expected_seconds' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
