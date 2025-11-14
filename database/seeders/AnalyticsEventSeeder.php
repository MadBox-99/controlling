<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AnalyticsEvent;
use App\Models\Team;
use Illuminate\Database\Seeder;

final class AnalyticsEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();

        foreach ($teams as $team) {
            AnalyticsEvent::factory()->count(60)->create([
                'team_id' => $team->id,
            ]);
        }
    }
}
