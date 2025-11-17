<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SearchQuery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SearchQuery>
 */
final class SearchQueryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $impressions = fake()->numberBetween(10, 10000);
        $clicks = fake()->numberBetween(1, (int) ($impressions * 0.3));
        $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;

        return [
            'team_id' => null,
            'date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'query' => fake()->words(rand(2, 5), true),
            'country' => fake()->countryCode(),
            'device' => fake()->randomElement(['desktop', 'mobile', 'tablet']),
            'impressions' => $impressions,
            'clicks' => $clicks,
            'ctr' => $ctr,
            'position' => fake()->randomFloat(2, 1, 100),
        ];
    }

    /**
     * Create a tracked query with specific text and device for time-series data.
     */
    public function tracked(string $queryText, string $device): static
    {
        $baseImpressions = rand(200, 1500);
        $variance = rand(70, 130) / 100;

        return $this->state(fn (array $attributes) => [
            'query' => $queryText,
            'country' => 'US',
            'device' => $device,
            'impressions' => (int) ($baseImpressions * $variance),
            'clicks' => (int) ($baseImpressions * $variance * rand(3, 12) / 100),
            'ctr' => rand(300, 1200) / 100,
            'position' => rand(15, 80) / 10,
        ]);
    }
}
