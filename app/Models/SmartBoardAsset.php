<?php

namespace App\Models;

use Database\Factories\SmartBoardAssetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['smart_board_id', 'disk', 'path', 'mime_type', 'size', 'original_name'])]
class SmartBoardAsset extends Model
{
    /** @use HasFactory<SmartBoardAssetFactory> */
    use HasFactory;

    public function smartBoard(): BelongsTo
    {
        return $this->belongsTo(SmartBoard::class);
    }
}
