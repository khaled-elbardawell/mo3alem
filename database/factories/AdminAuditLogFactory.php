<?php

namespace Database\Factories;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminAuditLog>
 */
class AdminAuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_id' => User::factory(),
            'action' => 'user.updated',
            'subject_type' => (new User)->getMorphClass(),
            'subject_id' => User::factory(),
            'before_values' => ['name' => 'قبل'],
            'after_values' => ['name' => 'بعد'],
            'ip_address' => fake()->ipv4(),
            'created_at' => now(),
        ];
    }
}
