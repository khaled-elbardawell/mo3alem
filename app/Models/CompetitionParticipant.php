<?php

namespace App\Models;

use Database\Factories\CompetitionParticipantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['competition_id', 'name', 'position', 'is_active'])]
class CompetitionParticipant extends Model
{
    /** @use HasFactory<CompetitionParticipantFactory> */
    use HasFactory;

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(CompetitionResult::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
