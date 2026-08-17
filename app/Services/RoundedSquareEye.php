<?php

namespace App\Services;

use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Path\Path;

final class RoundedSquareEye implements EyeInterface
{
    private const OUTER_RADIUS = 1.15;

    private const CUTOUT_RADIUS = 0.85;

    private const INNER_RADIUS = 0.6;

    public function getExternalPath(): Path
    {
        return $this->roundedSquare(-3.5, -3.5, 7, self::OUTER_RADIUS)
            ->append($this->roundedSquare(-2.5, -2.5, 5, self::CUTOUT_RADIUS));
    }

    public function getInternalPath(): Path
    {
        return $this->roundedSquare(-1.5, -1.5, 3, self::INNER_RADIUS);
    }

    private function roundedSquare(float $left, float $top, float $size, float $radius): Path
    {
        $right = $left + $size;
        $bottom = $top + $size;

        return (new Path)
            ->move($left + $radius, $top)
            ->line($right - $radius, $top)
            ->ellipticArc($radius, $radius, 0, false, true, $right, $top + $radius)
            ->line($right, $bottom - $radius)
            ->ellipticArc($radius, $radius, 0, false, true, $right - $radius, $bottom)
            ->line($left + $radius, $bottom)
            ->ellipticArc($radius, $radius, 0, false, true, $left, $bottom - $radius)
            ->line($left, $top + $radius)
            ->ellipticArc($radius, $radius, 0, false, true, $left + $radius, $top)
            ->close();
    }
}
