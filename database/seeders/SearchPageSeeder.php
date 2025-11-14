<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SearchPage;
use App\Models\Team;
use Illuminate\Database\Seeder;

final class SearchPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();

        foreach ($teams as $team) {
            SearchPage::factory()->count(40)->create([
                'team_id' => $team->id,
            ]);
        }
    }
}
