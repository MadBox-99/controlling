<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AnalyticsPageview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalyticsPageview>
 */
final class AnalyticsPageviewFactory extends Factory
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
            'date' => fake()->date(),
            'page_path' => fake()->url(),
            'page_title' => fake()->sentence(3),
            'pageviews' => fake()->numberBetween(0, 10000),
            'unique_pageviews' => fake()->numberBetween(0, 5000),
            'avg_time_on_page' => fake()->numberBetween(0, 600),
            'entrances' => fake()->numberBetween(0, 1000),
            'bounce_rate' => fake()->randomFloat(2, 0, 100),
            'exit_rate' => fake()->randomFloat(2, 0, 100),
        ];
    }
}
