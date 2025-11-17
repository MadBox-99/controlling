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

        // Define specific queries that will be tracked over time
        $trackedQueries = [
            'best laptop 2025',
            'seo tips for beginners',
            'web development services',
            'how to improve website ranking',
            'laravel tutorial',
            'php best practices',
            'responsive web design',
            'digital marketing agency',
        ];

        foreach ($teams as $team) {
            // Create data for the last 30 days
            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays($i);

                // Create data for each tracked query with varying metrics
                foreach ($trackedQueries as $queryText) {
                    // Create data for different devices
                    foreach (['desktop', 'mobile', 'tablet'] as $device) {
                        SearchQuery::factory()
                            ->tracked($queryText, $device)
                            ->create([
                                'team_id' => $team->id,
                                'date' => $date,
                            ]);
                    }
                }

                // Add some random queries too (for variety)
                $randomQueryCount = rand(3, 7);
                for ($j = 0; $j < $randomQueryCount; $j++) {
                    SearchQuery::factory()->create([
                        'team_id' => $team->id,
                        'date' => $date,
                    ]);
                }
            }
        }
    }
}
