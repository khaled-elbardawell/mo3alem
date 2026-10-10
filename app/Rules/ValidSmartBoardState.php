<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidSmartBoardState implements ValidationRule
{
    public const WIDGET_TYPES = [
        'text', 'clock', 'timer', 'stopwatch', 'traffic-light', 'work-mode',
        'scoreboard', 'dice', 'randomizer', 'groups', 'drawing', 'image',
        'embed', 'qr', 'poll',
    ];

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)
            || ($value['schemaVersion'] ?? null) !== 1
            || ! is_array($value['board'] ?? null)
            || ! is_array($value['widgets'] ?? null)) {
            $fail('حالة السبورة غير صالحة.');

            return;
        }

        if (strlen((string) json_encode($value)) > (int) config('resource_limits.smart_board_state_bytes', 524288)) {
            $fail('حجم بيانات السبورة أكبر من الحد المسموح.');

            return;
        }

        $widgets = $value['widgets'];

        if (! array_is_list($widgets) || count($widgets) > (int) config('resource_limits.smart_board_widgets', 40)) {
            $fail('عدد أدوات السبورة أكبر من الحد المسموح.');

            return;
        }

        $identifiers = [];

        foreach ($widgets as $widget) {
            if (! $this->validWidget($widget)) {
                $fail('تحتوي السبورة على أداة أو موضع غير صالح.');

                return;
            }

            if (in_array($widget['id'], $identifiers, true)) {
                $fail('معرّفات أدوات السبورة يجب أن تكون فريدة.');

                return;
            }

            $identifiers[] = $widget['id'];
        }
    }

    private function validWidget(mixed $widget): bool
    {
        if (! is_array($widget)
            || ! is_string($widget['id'] ?? null)
            || $widget['id'] === ''
            || mb_strlen($widget['id']) > 64
            || ! in_array($widget['type'] ?? null, self::WIDGET_TYPES, true)
            || ! is_array($widget['config'] ?? null)) {
            return false;
        }

        foreach (['x', 'y', 'width', 'height'] as $coordinate) {
            if (! is_numeric($widget[$coordinate] ?? null)) {
                return false;
            }
        }

        return $widget['x'] >= 0 && $widget['x'] <= 100
            && $widget['y'] >= 0 && $widget['y'] <= 100
            && $widget['width'] >= 5 && $widget['width'] <= 100
            && $widget['height'] >= 5 && $widget['height'] <= 100
            && is_int($widget['zIndex'] ?? null)
            && $widget['zIndex'] >= 1 && $widget['zIndex'] <= 1000
            && is_bool($widget['locked'] ?? null)
            && strlen((string) json_encode($widget['config'])) <= 65536;
    }
}
