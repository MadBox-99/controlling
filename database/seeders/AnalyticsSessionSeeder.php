<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AnalyticsSession;
use App\Models\Team;
use Illuminate\Database\Seeder;

final class AnalyticsSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();

        foreach ($teams as $team) {
            AnalyticsSession::factory()->count(30)->create([
                'team_id' => $team->id,
            ]);
        }
    }
}
