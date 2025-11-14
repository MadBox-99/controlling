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

        foreach ($teams as $team) {
            AnalyticsConversion::factory()->count(5)->create([
                'team_id' => $team->id,
            ]);
        }
    }
}
