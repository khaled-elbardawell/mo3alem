<?php

namespace App;

enum SeoPage: string
{
    case Home = 'home';
    case Wheel = 'wheel';
    case Qr = 'qr';
    case ShortUrl = 'short_url';
    case Certificates = 'certificates';
    case SmartBoard = 'smart_board';

    public function label(): string
    {
        return match ($this) {
            self::Home => 'الصفحة الرئيسية',
            self::Wheel => 'عجلة الأسماء',
            self::Qr => 'إنشاء رمز QR',
            self::ShortUrl => 'اختصار الروابط',
            self::Certificates => 'إنشاء الشهادات',
            self::SmartBoard => 'السبورة الذكية',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Home => 'واجهة المنصة ونقطة الدخول الرئيسية للزوار.',
            self::Wheel => 'أداة الاختيار العشوائي وعجلة الأسماء.',
            self::Qr => 'أداة إنشاء وتصميم رموز QR.',
            self::ShortUrl => 'أداة اختصار الروابط وإنشاء QR وتتبع الزيارات.',
            self::Certificates => 'أداة تصميم الشهادات وطباعتها.',
            self::SmartBoard => 'مساحة صفية عربية تفاعلية تجمع أدوات إدارة الحصة في شاشة واحدة.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Home => 'fa-house',
            self::Wheel => 'fa-dharmachakra',
            self::Qr => 'fa-qrcode',
            self::ShortUrl => 'fa-link',
            self::Certificates => 'fa-award',
            self::SmartBoard => 'fa-chalkboard-user',
        };
    }

    public function routeName(): string
    {
        return match ($this) {
            self::Home => 'home',
            self::Wheel => 'tools.wheel',
            self::Qr => 'tools.qr',
            self::ShortUrl => 'tools.short-url',
            self::Certificates => 'tools.certificates',
            self::SmartBoard => 'tools.smart-board',
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
            self::ShortUrl => 'اختصار الروابط وإنشاء QR | معلم',
            self::Certificates => 'إنشاء شهادات احترافية | معلم',
            self::SmartBoard => 'السبورة الذكية للمعلمين | معلم',
        };
    }

    private function defaultDescription(): string
    {
        return match ($this) {
            self::Home => 'منصة أدوات تعليمية عربية تساعد المعلم على إنشاء الأنشطة ورموز QR والشهادات بسهولة واحترافية.',
            self::Wheel => 'أضف الأسماء وأدر عجلة الأسماء العشوائية لاختيار اسم بسرعة ووضوح داخل الصف.',
            self::Qr => 'أنشئ رمز QR احترافياً وخصص تصميمه ثم نزّله أو احفظه في حسابك بسهولة.',
            self::ShortUrl => 'اختصر روابطك بمقطع مخصص، أنشئ رمز QR، وتابع الزيارات بإحصائيات تحافظ على الخصوصية.',
            self::Certificates => 'صمم شهادات احترافية بقوالب جاهزة، وخصص النصوص ثم نزّلها أو اطبعها بجودة عالية.',
            self::SmartBoard => 'سبورة ذكية عربية لإدارة الحصة بالمؤقت والتعليمات والاختيار العشوائي والاستطلاع المباشر وأدوات صفية سهلة.',
        };
    }

    private function defaultKeywords(): string
    {
        return match ($this) {
            self::Home => 'أدوات المعلم، عجلة الأسماء، إنشاء QR، إنشاء شهادات، معلم',
            self::Wheel => 'عجلة الأسماء، اختيار عشوائي، عجلة الحظ، أدوات المعلم',
            self::Qr => 'إنشاء QR، رمز QR، باركود، مولد QR',
            self::ShortUrl => 'اختصار الروابط، رابط قصير، إنشاء QR للرابط، إحصائيات الروابط',
            self::Certificates => 'إنشاء شهادات، تصميم شهادات، شهادات تقدير، قوالب شهادات',
            self::SmartBoard => 'سبورة ذكية، أدوات المعلم، إدارة الصف، مؤقت صفّي، استطلاع طلابي',
        };
    }
}
