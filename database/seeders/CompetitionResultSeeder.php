<?php

namespace Database\Seeders;

use App\Models\Competition;
use Illuminate\Database\Seeder;

class CompetitionResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Competition::factory()->create([
            'names' => ['أحمد', 'سارة'],
            'names_count' => 2,
            'results' => [[
                'round' => 1,
                'name' => 'أحمد',
                'date' => now()->toISOString(),
                'position' => 1,
            ]],
            'results_count' => 1,
            'status' => 'active',
        ]);
    }
}
