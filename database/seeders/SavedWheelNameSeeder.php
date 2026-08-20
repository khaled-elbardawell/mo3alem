<?php

namespace Database\Seeders;

use App\Models\SavedWheel;
use Illuminate\Database\Seeder;

class SavedWheelNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SavedWheel::factory()->create([
            'names' => ['أحمد', 'سارة', 'محمد'],
            'names_count' => 3,
        ]);
    }
}
