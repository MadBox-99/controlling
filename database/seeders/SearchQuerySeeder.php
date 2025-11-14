<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SearchQuery;
use App\Models\Team;
use Illuminate\Database\Seeder;

final class SearchQuerySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();

        foreach ($teams as $team) {
            SearchQuery::factory()->count(50)->create([
                'team_id' => $team->id,
            ]);
        }
    }
}
