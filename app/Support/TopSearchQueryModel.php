<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\SearchQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Sushi\Sushi;

/**
 * Temporary model for Sushi to work with aggregated Search Query data
 */
final class TopSearchQueryModel extends Model
{
    use Sushi;

    protected $schema = [
        'query' => 'string',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'ctr' => 'float',
        'position' => 'float',
    ];

    public function getRows(): array
    {
        $startDate = $this->getStartDate();

        return SearchQuery::query()
            ->select('query', DB::raw('SUM(impressions) as total_impressions'), DB::raw('SUM(clicks) as total_clicks'), DB::raw('AVG(ctr) as avg_ctr'), DB::raw('AVG(position) as avg_position'))
            ->where('date', '>=', $startDate)
            ->groupBy('query')
            ->orderByDesc('total_clicks')
            ->limit(100)
            ->get()
            ->map(function ($item, $index) {
                return [
                    'id' => $index + 1,
                    'query' => $item->query,
                    'impressions' => (int) $item->total_impressions,
                    'clicks' => (int) $item->total_clicks,
                    'ctr' => round((float) $item->avg_ctr, 2),
                    'position' => round((float) $item->avg_position, 2),
                ];
            })
            ->toArray();
    }

    protected function getStartDate(): \Carbon\Carbon
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
