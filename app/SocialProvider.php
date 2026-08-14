<?php

namespace App;

enum SocialProvider: string
{
    case Google = 'google';
    case Facebook = 'facebook';

    public function userIdColumn(): string
    {
        return match ($this) {
            self::Google => 'google_id',
            self::Facebook => 'facebook_id',
        };
    }
}
