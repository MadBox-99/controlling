<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AnalyticsConversion;
use App\Models\Team;
use Illuminate\Database\Seeder;

final class AnalyticsConversionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();
        $goalNames = ['Contact Form', 'Newsletter Signup', 'Purchase', 'Download', 'Registration'];

        foreach ($teams as $team) {
            foreach ($goalNames as $index => $goalName) {
                $date = now()->subMonths($team->id)->subDays($index)->format('Y-m-d');

                AnalyticsConversion::query()->firstOrCreate(
                    ['date' => $date, 'goal_name' => $goalName],
                    [
                        'team_id' => $team->id,
                        'goal_completions' => fake()->numberBetween(1, 100),
                        'goal_value' => fake()->randomFloat(2, 10, 5000),
                        'conversion_rate' => fake()->randomFloat(2, 0.5, 25),
                    ],
                );
            }
        }
    }
}
