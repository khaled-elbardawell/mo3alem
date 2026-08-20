<?php

namespace App\Models;

use Database\Factories\CompetitionResultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'competition_id',
    'competition_participant_id',
    'round',
    'sort_order',
    'name_snapshot',
    'position',
    'won_at',
])]
class CompetitionResult extends Model
{
    /** @use HasFactory<CompetitionResultFactory> */
    use HasFactory;

    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(CompetitionParticipant::class, 'competition_participant_id');
    }

    protected function casts(): array
    {
        return [
            'won_at' => 'immutable_datetime',
        ];
    }
}
