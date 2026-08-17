<?php

namespace Database\Seeders;

use App\Models\CertificateTemplate;
use App\Models\QrTemplate;
use Illuminate\Database\Seeder;

class ToolTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (range(1, 10) as $number) {
            CertificateTemplate::withTrashed()->firstOrCreate(
                ['key' => "b{$number}"],
                [
                    'label' => "قالب {$number}",
                    'image_path' => "assets/certificate-templates/b{$number}.jpg",
                    'is_builtin' => true,
                    'width' => 1123,
                    'height' => 794,
                    'sort_order' => $number,
                    'is_active' => true,
                ],
            );
        }

        $layouts = [
            1 => [460, 700, 1060], 2 => [450, 430, 1050], 3 => [440, 480, 1000],
            4 => [430, 440, 1050], 5 => [420, 420, 1100], 6 => [450, 450, 1000],
            7 => [450, 380, 1050], 8 => [500, 150, 930], 9 => [480, 450, 1000],
            10 => [450, 350, 1050], 11 => [585, 650, 900],
        ];

        foreach ($layouts as $number => $layout) {
            QrTemplate::withTrashed()->firstOrCreate(
                ['key' => "template-{$number}"],
                [
                    'label' => "قالب {$number}",
                    'image_path' => "assets/qr-templates/{$number}.png",
                    'is_builtin' => true,
                    'width' => 1968,
                    'height' => 1968,
                    'qr_x' => $layout[0],
                    'qr_y' => $layout[1],
                    'qr_size' => $layout[2],
                    'sort_order' => $number,
                    'is_active' => true,
                ],
            );
        }
    }
}
