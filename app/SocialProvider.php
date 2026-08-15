<?php

namespace App;

enum SocialProvider: string
{
    case Google = 'google';

    public function userIdColumn(): string
    {
        return match ($this) {
            self::Google => 'google_id',
        };
    }
}
