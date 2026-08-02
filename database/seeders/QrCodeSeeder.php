<?php

namespace Database\Seeders;

use App\Models\QrCode;
use App\Models\User;
use Illuminate\Database\Seeder;

class QrCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->where('role', 'user')->each(function (User $user): void {
            QrCode::factory()->for($user)->count(2)->create();
        });
    }
}
