<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Settings;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Settings>
 */
final class SettingsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'property_id' => (string) fake()->randomNumber(9),
            'google_tag_id' => 'G-' . mb_strtoupper(fake()->bothify('########')),
            'site_url' => fake()->url(),
            'last_sync_at' => fake()->dateTimeThisMonth(),
        ];
    }
}
