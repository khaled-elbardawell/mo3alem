<?php

namespace Database\Factories;

use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShortLink>
 */
class ShortLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'destination_url' => fake()->url(),
            'slug' => Str::lower(Str::random(7)),
            'is_active' => true,
            'expires_at' => null,
            'visit_count' => 0,
            'unique_visit_count' => 0,
            'last_visited_at' => null,
            'version' => 1,
        ];
    }
}
