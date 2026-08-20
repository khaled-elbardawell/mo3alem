<?php

namespace Database\Seeders;

use App\Models\Competition;
use Illuminate\Database\Seeder;

class CompetitionParticipantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Competition::factory()->create([
            'names' => ['أحمد', 'سارة', 'محمد'],
            'names_count' => 3,
        ]);
    }
}
