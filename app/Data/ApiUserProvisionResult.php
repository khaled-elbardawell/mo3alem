<?php

namespace App\Data;

use App\Models\ExternalUserLink;
use App\Models\User;

final readonly class ApiUserProvisionResult
{
    public function __construct(
        public User $user,
        public ExternalUserLink $link,
        public bool $created,
        public ?string $temporaryPassword,
    ) {}
}
