<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        DB::table('certificate_templates')->insert(
            collect(range(1, 10))->map(fn (int $number): array => [
                'key' => "b{$number}",
                'label' => "قالب {$number}",
                'image_path' => "assets/certificate-templates/b{$number}.jpg",
                'is_builtin' => true,
                'width' => 1123,
                'height' => 794,
                'sort_order' => $number,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
        );

        $layouts = [
            1 => [460, 700, 1060],
            2 => [450, 430, 1050],
            3 => [440, 480, 1000],
            4 => [430, 440, 1050],
            5 => [420, 420, 1100],
            6 => [450, 450, 1000],
            7 => [450, 380, 1050],
            8 => [500, 150, 930],
            9 => [480, 450, 1000],
            10 => [450, 350, 1050],
            11 => [585, 650, 900],
        ];

        DB::table('qr_templates')->insert(
            collect($layouts)->map(fn (array $layout, int $number): array => [
                'key' => "template-{$number}",
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
                'created_at' => $now,
                'updated_at' => $now,
            ])->values()->all(),
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('certificate_templates')
            ->whereIn('key', collect(range(1, 10))->map(fn (int $number): string => "b{$number}"))
            ->delete();
        DB::table('qr_templates')
            ->whereIn('key', collect(range(1, 11))->map(fn (int $number): string => "template-{$number}"))
            ->delete();
    }
};
