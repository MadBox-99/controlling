<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AnalyticsConversion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalyticsConversion>
 */
final class AnalyticsConversionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $goalCompletions = fake()->numberBetween(1, 100);
        $goalValue = fake()->randomFloat(2, 10, 5000);
        $conversionRate = fake()->randomFloat(2, 0.5, 25);

        return [
            'team_id' => null,
            'date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'goal_name' => fake()->randomElement(['Contact Form', 'Newsletter Signup', 'Purchase', 'Download', 'Registration']),
            'goal_completions' => $goalCompletions,
            'goal_value' => $goalValue,
            'conversion_rate' => $conversionRate,
        ];
    }
}
