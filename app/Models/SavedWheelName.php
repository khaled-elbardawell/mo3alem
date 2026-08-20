<?php

namespace App\Models;

use Database\Factories\SavedWheelNameFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['saved_wheel_id', 'name', 'position'])]
class SavedWheelName extends Model
{
    /** @use HasFactory<SavedWheelNameFactory> */
    use HasFactory;

    public function savedWheel(): BelongsTo
    {
        return $this->belongsTo(SavedWheel::class);
    }
}
