<?php

namespace App\Models;

use Database\Factories\ExternalUserLinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['api_client_id', 'user_id', 'external_id', 'invitation_sent_at'])]
class ExternalUserLink extends Model
{
    /** @use HasFactory<ExternalUserLinkFactory> */
    use HasFactory;

    public function apiClient(): BelongsTo
    {
        return $this->belongsTo(ApiClient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    protected function casts(): array
    {
        return [
            'invitation_sent_at' => 'datetime',
        ];
    }
}
