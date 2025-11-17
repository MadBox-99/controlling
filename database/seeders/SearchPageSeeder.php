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

        // Define specific pages that will be tracked over time
        $trackedPages = [
            '/products/laptop',
            '/blog/seo-tips',
            '/services/web-development',
            '/contact',
            '/about-us',
            '/pricing',
            '/features',
            '/documentation/getting-started',
        ];

        foreach ($teams as $team) {
            // Create data for the last 30 days
            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays($i);

                // Create data for each tracked page with varying metrics
                foreach ($trackedPages as $pageUrl) {
                    // Create data for different devices
                    foreach (['desktop', 'mobile', 'tablet'] as $device) {
                        SearchPage::factory()
                            ->tracked($pageUrl, $device)
                            ->create([
                                'team_id' => $team->id,
                                'date' => $date,
                            ]);
                    }
                }

                // Add some random pages too (for variety)
                $randomPageCount = rand(2, 5);
                for ($j = 0; $j < $randomPageCount; $j++) {
                    SearchPage::factory()->create([
                        'team_id' => $team->id,
                        'date' => $date,
                    ]);
                }
            }
        }
    }
}
