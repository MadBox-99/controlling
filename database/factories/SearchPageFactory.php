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

    /**
     * Create a tracked page with specific URL and device for time-series data.
     */
    public function tracked(string $pageUrl, string $device): static
    {
        $baseImpressions = rand(100, 1000);
        $variance = rand(80, 120) / 100;

        return $this->state(fn (array $attributes) => [
            'page_url' => $pageUrl,
            'country' => 'US',
            'device' => $device,
            'impressions' => (int) ($baseImpressions * $variance),
            'clicks' => (int) ($baseImpressions * $variance * rand(5, 15) / 100),
            'ctr' => rand(500, 1500) / 100,
            'position' => rand(10, 50) / 10,
        ]);
    }
}
