<?php

namespace App;

enum FooterLinkPlatform: string
{
    case Website = 'website';
    case Facebook = 'facebook';
    case Instagram = 'instagram';
    case X = 'x';
    case Youtube = 'youtube';
    case Tiktok = 'tiktok';
    case Linkedin = 'linkedin';
    case Whatsapp = 'whatsapp';
    case Telegram = 'telegram';
    case Threads = 'threads';
    case Snapchat = 'snapchat';
    case Tools = 'tools';
    case Help = 'help';

    public function label(): string
    {
        return match ($this) {
            self::Website => 'موقع إلكتروني',
            self::Facebook => 'Facebook',
            self::Instagram => 'Instagram',
            self::X => 'X (Twitter)',
            self::Youtube => 'YouTube',
            self::Tiktok => 'TikTok',
            self::Linkedin => 'LinkedIn',
            self::Whatsapp => 'WhatsApp',
            self::Telegram => 'Telegram',
            self::Threads => 'Threads',
            self::Snapchat => 'Snapchat',
            self::Tools => 'الأدوات',
            self::Help => 'المساعدة',
        };
    }

    public function iconClass(): string
    {
        return match ($this) {
            self::Website => 'fa-solid fa-globe',
            self::Facebook => 'fa-brands fa-facebook-f',
            self::Instagram => 'fa-brands fa-instagram',
            self::X => 'fa-brands fa-x-twitter',
            self::Youtube => 'fa-brands fa-youtube',
            self::Tiktok => 'fa-brands fa-tiktok',
            self::Linkedin => 'fa-brands fa-linkedin-in',
            self::Whatsapp => 'fa-brands fa-whatsapp',
            self::Telegram => 'fa-brands fa-telegram',
            self::Threads => 'fa-brands fa-threads',
            self::Snapchat => 'fa-brands fa-snapchat',
            self::Tools => 'fa-solid fa-toolbox',
            self::Help => 'fa-regular fa-circle-question',
        };
    }
}
