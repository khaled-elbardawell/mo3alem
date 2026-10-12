<?php

namespace Database\Factories;

use App\Models\SmartBoardSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SmartBoardSession>
 */
class SmartBoardSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'smart_board_id' => null,
            'user_id' => null,
            'public_code' => Str::upper(Str::random(6)),
            'owner_token_hash' => hash('sha256', 'owner-token'),
            'status' => 'active',
            'poll_key' => 'poll-1',
            'poll_snapshot' => [
                'question' => 'هل أنت مستعد؟',
                'options' => [
                    ['key' => 'yes', 'label' => 'نعم'],
                    ['key' => 'no', 'label' => 'ليس بعد'],
                ],
            ],
            'version' => 1,
            'expires_at' => now()->addHours(2),
        ];
    }
}
