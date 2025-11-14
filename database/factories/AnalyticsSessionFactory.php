<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AnalyticsSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalyticsSession>
 */
final class AnalyticsSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => null,
            'date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'sessions' => fake()->numberBetween(10, 1000),
            'users' => fake()->numberBetween(5, 800),
            'new_users' => fake()->numberBetween(0, 500),
            'bounce_rate' => fake()->randomFloat(2, 20, 80),
            'avg_session_duration' => fake()->numberBetween(30, 600),
            'pages_per_session' => fake()->randomFloat(2, 1, 10),
            'source' => fake()->randomElement(['google', 'facebook', 'twitter', 'direct', 'email', 'referral']),
            'medium' => fake()->randomElement(['organic', 'cpc', 'social', 'email', 'referral', '(none)']),
            'campaign' => fake()->optional()->word(),
        ];
    }
}
