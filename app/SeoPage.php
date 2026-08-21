<?php

namespace App;

enum SeoPage: string
{
    case Home = 'home';
    case Wheel = 'wheel';
    case Qr = 'qr';
    case Certificates = 'certificates';

    public function label(): string
    {
        return match ($this) {
            self::Home => 'الصفحة الرئيسية',
            self::Wheel => 'عجلة الأسماء',
            self::Qr => 'إنشاء رمز QR',
            self::Certificates => 'إنشاء الشهادات',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Home => 'واجهة المنصة ونقطة الدخول الرئيسية للزوار.',
            self::Wheel => 'أداة الاختيار العشوائي وعجلة الأسماء.',
            self::Qr => 'أداة إنشاء وتصميم رموز QR.',
            self::Certificates => 'أداة تصميم الشهادات وطباعتها.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Home => 'fa-house',
            self::Wheel => 'fa-dharmachakra',
            self::Qr => 'fa-qrcode',
            self::Certificates => 'fa-award',
        };
    }

    public function routeName(): string
    {
        return match ($this) {
            self::Home => 'home',
            self::Wheel => 'tools.wheel',
            self::Qr => 'tools.qr',
            self::Certificates => 'tools.certificates',
        };
    }

    /** @return array<string, mixed> */
    public function defaults(): array
    {
        return [
            'page_key' => $this->value,
            'site_name' => 'معلم',
            'title' => $this->defaultTitle(),
            'description' => $this->defaultDescription(),
            'keywords' => $this->defaultKeywords(),
            'allow_indexing' => true,
            'allow_following' => true,
            'include_in_sitemap' => true,
            'sitemap_change_frequency' => 'weekly',
            'sitemap_priority' => $this === self::Home ? 1.0 : 0.9,
            'twitter_card' => 'summary_large_image',
        ];
    }

    private function defaultTitle(): string
    {
        return match ($this) {
            self::Home => 'معلم | أدوات ذكية لكل معلم',
            self::Wheel => 'عجلة الأسماء العشوائية | معلم',
            self::Qr => 'إنشاء رمز QR احترافي | معلم',
            self::Certificates => 'إنشاء شهادات احترافية | معلم',
        };
    }

    private function defaultDescription(): string
    {
        return match ($this) {
            self::Home => 'منصة أدوات تعليمية عربية تساعد المعلم على إنشاء الأنشطة ورموز QR والشهادات بسهولة واحترافية.',
            self::Wheel => 'أضف الأسماء وأدر عجلة الأسماء العشوائية لاختيار اسم بسرعة ووضوح داخل الصف.',
            self::Qr => 'أنشئ رمز QR احترافياً وخصص تصميمه ثم نزّله أو احفظه في حسابك بسهولة.',
            self::Certificates => 'صمم شهادات احترافية بقوالب جاهزة، وخصص النصوص ثم نزّلها أو اطبعها بجودة عالية.',
        };
    }

    private function defaultKeywords(): string
    {
        return match ($this) {
            self::Home => 'أدوات المعلم، عجلة الأسماء، إنشاء QR، إنشاء شهادات، معلم',
            self::Wheel => 'عجلة الأسماء، اختيار عشوائي، عجلة الحظ، أدوات المعلم',
            self::Qr => 'إنشاء QR، رمز QR، باركود، مولد QR',
            self::Certificates => 'إنشاء شهادات، تصميم شهادات، شهادات تقدير، قوالب شهادات',
        };
    }
}
