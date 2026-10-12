<?php

namespace App\Models;

use Database\Factories\SmartBoardPollResponseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['smart_board_session_id', 'poll_key', 'option_key', 'participant_hash'])]
class SmartBoardPollResponse extends Model
{
    /** @use HasFactory<SmartBoardPollResponseFactory> */
    use HasFactory;

    public function session(): BelongsTo
    {
        return $this->belongsTo(SmartBoardSession::class, 'smart_board_session_id');
    }
}
