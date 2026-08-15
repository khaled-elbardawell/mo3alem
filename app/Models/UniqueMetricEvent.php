<?php

namespace App\Models;

use Database\Factories\UniqueMetricEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['date', 'event_hash'])]
class UniqueMetricEvent extends Model
{
    /** @use HasFactory<UniqueMetricEventFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
