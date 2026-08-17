<?php

namespace App\Models;

use Database\Factories\QrTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable(['key', 'label', 'image_path', 'is_builtin', 'width', 'height', 'qr_x', 'qr_y', 'qr_size', 'sort_order', 'is_active'])]
class QrTemplate extends Model
{
    /** @use HasFactory<QrTemplateFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'is_builtin' => false,
        'sort_order' => 0,
        'is_active' => true,
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function imageUrl(): string
    {
        return $this->is_builtin
            ? asset($this->image_path)
            : Storage::disk('public')->url($this->image_path);
    }

    /** @return array{key: string, label: string, url: string, width: int, height: int, qrX: int, qrY: int, qrSize: int} */
    public function toToolConfig(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'url' => $this->imageUrl(),
            'width' => $this->width,
            'height' => $this->height,
            'qrX' => $this->qr_x,
            'qrY' => $this->qr_y,
            'qrSize' => $this->qr_size,
        ];
    }

    protected function casts(): array
    {
        return [
            'is_builtin' => 'boolean',
            'width' => 'integer',
            'height' => 'integer',
            'qr_x' => 'integer',
            'qr_y' => 'integer',
            'qr_size' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
