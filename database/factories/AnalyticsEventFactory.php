<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AnalyticsEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalyticsEvent>
 */
final class AnalyticsEventFactory extends Factory
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
            'event_name' => fake()->randomElement(['page_view', 'click', 'form_submit', 'download', 'video_play']),
            'event_category' => fake()->randomElement(['engagement', 'conversion', 'navigation', 'media']),
            'event_action' => fake()->randomElement(['click', 'submit', 'view', 'download', 'play', 'pause']),
            'event_label' => fake()->optional()->words(2, true),
            'event_count' => fake()->numberBetween(1, 500),
            'event_value' => fake()->optional()->randomFloat(2, 0, 1000),
        ];
    }
}
