<?php

namespace Database\Factories;

use App\Models\ApiClient;
use App\Models\ExternalUserLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExternalUserLink>
 */
class ExternalUserLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'api_client_id' => ApiClient::factory(),
            'user_id' => User::factory(),
            'external_id' => fake()->unique()->uuid(),
            'invitation_sent_at' => now(),
        ];
    }
}
