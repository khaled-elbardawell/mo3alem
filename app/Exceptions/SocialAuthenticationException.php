<?php

namespace App\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class SocialAuthenticationException extends RuntimeException implements ShouldntReport
{
    public static function missingIdentity(): self
    {
        return new self('تعذر الحصول على معلومات الحساب المطلوبة من مزوّد تسجيل الدخول.');
    }

    public static function providerAlreadyLinked(): self
    {
        return new self('هذا الحساب مربوط مسبقًا بحساب آخر لدى مزوّد تسجيل الدخول نفسه.');
    }

    public static function unavailableAccount(): self
    {
        return new self('هذا الحساب غير متاح لتسجيل الدخول. تواصل مع الإدارة إذا كنت تعتقد أن هناك خطأ.');
    }
}
