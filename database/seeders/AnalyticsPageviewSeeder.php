<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AnalyticsPageview;
use App\Models\Team;
use Illuminate\Database\Seeder;

final class AnalyticsPageviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();

        foreach ($teams as $team) {
            AnalyticsPageview::factory()->count(50)->create([
                'team_id' => $team->id,
            ]);
        }
    }
}
