<?php

namespace Database\Factories;

use App\Models\SmartBoardPollResponse;
use App\Models\SmartBoardSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SmartBoardPollResponse>
 */
class SmartBoardPollResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'smart_board_session_id' => SmartBoardSession::factory(),
            'poll_key' => 'poll-1',
            'option_key' => 'yes',
            'participant_hash' => hash('sha256', fake()->uuid()),
        ];
    }
}
