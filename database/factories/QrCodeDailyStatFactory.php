<?php

namespace Database\Factories;

use App\Models\QrCode;
use App\Models\QrCodeDailyStat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QrCodeDailyStat>
 */
class QrCodeDailyStatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $scanCount = fake()->numberBetween(1, 100);

        return [
            'qr_code_id' => QrCode::factory(),
            'date' => fake()->dateTimeBetween('-30 days'),
            'scans' => $scanCount,
            'unique_scans' => fake()->numberBetween(1, $scanCount),
        ];
    }
}
