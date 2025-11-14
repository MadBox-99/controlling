<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SearchPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SearchPage>
 */
final class SearchPageFactory extends Factory
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
            'page_url' => fake()->url(),
            'country' => fake()->countryCode(),
            'device' => fake()->randomElement(['desktop', 'mobile', 'tablet']),
            'impressions' => fake()->numberBetween(0, 10000),
            'clicks' => fake()->numberBetween(0, 1000),
            'ctr' => fake()->randomFloat(2, 0, 100),
            'position' => fake()->randomFloat(2, 1, 100),
        ];
    }
}
