<?php

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Exception\WriterException;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Eye\SimpleCircleEye;
use BaconQrCode\Renderer\Eye\SquareEye;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Module\DotsModule;
use BaconQrCode\Renderer\Module\ModuleInterface;
use BaconQrCode\Renderer\Module\RoundnessModule;
use BaconQrCode\Renderer\Module\SquareModule;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Validation\ValidationException;

class QrCodeRenderer
{
    private const ROUNDED_MODULE_INTENSITY = 0.7;

    /** @param array<string, mixed> $design */
    public function render(string $content, array $design): string
    {
        [$module, $eye] = $this->shapes($design['style']);
        $background = $this->color($design['background_color']);
        $foreground = $this->color($design['foreground_color']);
        $eyeColor = $this->color($design['eye_color']);
        $eyeFill = EyeFill::uniform($eyeColor);
        $fill = Fill::withForegroundColor($background, $foreground, $eyeFill, $eyeFill, $eyeFill);
        $renderer = new ImageRenderer(
            new RendererStyle(720, 4, $module, $eye, $fill),
            new SvgImageBackEnd,
        );

        try {
            return (new Writer($renderer))->writeString(
                $content,
                'UTF-8',
                ErrorCorrectionLevel::H(),
            );
        } catch (WriterException) {
            throw ValidationException::withMessages([
                'payload' => 'المحتوى طويل جدًا لإنشاء رمز قابل للمسح. اختصره ثم حاول مجددًا.',
            ]);
        }
    }

    /** @return array{ModuleInterface, EyeInterface} */
    private function shapes(string $style): array
    {
        return match ($style) {
            'dots' => [new DotsModule(DotsModule::MEDIUM), SimpleCircleEye::instance()],
            'rounded' => $this->roundedShapes(),
            default => [SquareModule::instance(), SquareEye::instance()],
        };
    }

    /** @return array{ModuleInterface, EyeInterface} */
    private function roundedShapes(): array
    {
        return [
            new RoundnessModule(self::ROUNDED_MODULE_INTENSITY),
            new RoundedSquareEye,
        ];
    }

    private function color(string $hex): Rgb
    {
        [$red, $green, $blue] = sscanf($hex, '#%02x%02x%02x');

        return new Rgb($red, $green, $blue);
    }
}
