<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Kpi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kpi>
 */
final class KpiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fromDate = fake()->dateTimeBetween('-1 month', 'now');
        $targetDate = fake()->dateTimeBetween('now', '+3 months');

        return [
            'team_id' => null,
            'code' => fake()->unique()->word(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'data_source' => fake()->randomElement(['analytics', 'search_console', 'manual', 'calculated']),
            'category' => fake()->randomElement(['traffic', 'engagement', 'conversion', 'seo', 'custom']),
            'format' => fake()->randomElement(['number', 'percentage', 'ratio', 'duration']),
            'target_value' => fake()->randomFloat(2, 0, 10000),
            'target_date' => $targetDate,
            'from_date' => $fromDate,
            'comparison_start_date' => fake()->dateTimeBetween('-2 months', '-1 month'),
            'comparison_end_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'goal_type' => fake()->randomElement(['increase', 'decrease']),
            'value_type' => fake()->randomElement(['percentage', 'fixed']),
            'page_path' => null,
            'metric_type' => null,
            'is_active' => true,
        ];
    }
}
