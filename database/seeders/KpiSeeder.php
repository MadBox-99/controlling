<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Kpi;
use App\Models\Team;
use Illuminate\Database\Seeder;

final class KpiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();

        foreach ($teams as $team) {
            Kpi::factory()->count(10)->create([
                'team_id' => $team->id,
            ]);
        }
    }
}
