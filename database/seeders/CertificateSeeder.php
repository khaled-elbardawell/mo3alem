<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Database\Seeder;

class CertificateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->first();

        if ($user) {
            Certificate::factory()->for($user)->count(3)->create();
        }
    }
}
