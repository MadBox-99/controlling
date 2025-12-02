<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SearchSitemap;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SearchSitemap>
 */
final class SearchSitemapFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sitemap_url' => fake()->url() . '/sitemap.xml',
            'last_submitted' => fake()->dateTimeThisMonth(),
            'is_pending' => fake()->boolean(),
            'warnings' => fake()->numberBetween(0, 10),
            'errors' => fake()->numberBetween(0, 5),
        ];
    }
}
