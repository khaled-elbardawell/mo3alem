<?php

namespace App\Models;

use Database\Factories\ApiClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

#[Fillable(['name', 'allowed_ips', 'token_expiration_days', 'is_active', 'last_used_at'])]
class ApiClient extends Authenticatable
{
    /** @use HasFactory<ApiClientFactory> */
    use HasApiTokens, HasFactory, SoftDeletes;

    public const AbilityCreateUsers = 'users:create';

    protected $attributes = [
        'token_expiration_days' => 90,
        'is_active' => true,
    ];

    public function externalUserLinks(): HasMany
    {
        return $this->hasMany(ExternalUserLink::class);
    }

    public function latestToken(): MorphOne
    {
        return $this->morphOne(PersonalAccessToken::class, 'tokenable')->latestOfMany();
    }

    public function acceptsIp(string $ipAddress): bool
    {
        return empty($this->allowed_ips) || in_array($ipAddress, $this->allowed_ips, true);
    }

    protected function casts(): array
    {
        return [
            'allowed_ips' => 'array',
            'token_expiration_days' => 'integer',
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }
}
