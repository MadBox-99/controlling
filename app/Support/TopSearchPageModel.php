<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\SearchPage;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Sushi\Sushi;

/**
 * Temporary model for Sushi to work with aggregated Search Page data
 */
final class TopSearchPageModel extends Model
{
    use Sushi;

    protected $schema = [
        'page_url' => 'string',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'ctr' => 'float',
        'position' => 'float',
    ];

    public function getRows(): array
    {
        $startDate = $this->getStartDate();

        return SearchPage::query()
            ->select('page_url', DB::raw('SUM(impressions) as total_impressions'), DB::raw('SUM(clicks) as total_clicks'), DB::raw('AVG(ctr) as avg_ctr'), DB::raw('AVG(position) as avg_position'))
            ->where('date', '>=', $startDate)
            ->groupBy('page_url')
            ->orderByDesc('total_clicks')
            ->limit(100)
            ->get()
            ->map(fn ($item, $index): array => [
                'id' => $index + 1,
                'page_url' => $item->page_url,
                'impressions' => (int) $item->total_impressions,
                'clicks' => (int) $item->total_clicks,
                'ctr' => round((float) $item->avg_ctr, 2),
                'position' => round((float) $item->avg_position, 2),
            ])
            ->toArray();
    }

    private function getStartDate(): CarbonInterface
    {
        $dateRangeType = session('search_console_date_range', '28_days');

        return match ($dateRangeType) {
            '24_hours' => now()->subHours(24),
            '7_days' => now()->subDays(7),
            '28_days' => now()->subDays(28),
            '3_months' => now()->subMonths(3),
            default => now()->subDays(28),
        };
    }
}
