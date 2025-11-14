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
        return [
            'team_id' => null,
            'code' => fake()->unique()->word(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'data_source' => fake()->randomElement(['analytics', 'search_console', 'manual', 'calculated']),
            'category' => fake()->randomElement(['traffic', 'engagement', 'conversion', 'seo', 'custom']),
            'target_value' => fake()->randomFloat(2, 0, 10000),
            'target_date' => null,
            'from_date' => null,
            'goal_type' => null,
            'value_type' => null,
            'page_path' => null,
            'metric_type' => null,
            'is_active' => true,
        ];
    }
}
