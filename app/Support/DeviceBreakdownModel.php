<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\SearchQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Sushi\Sushi;

/**
 * Temporary model for Sushi to work with device breakdown data
 */
final class DeviceBreakdownModel extends Model
{
    use Sushi;

    protected $schema = [
        'device' => 'string',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'ctr' => 'float',
    ];

    public function getRows(): array
    {
        $thirtyDaysAgo = now()->subDays(30);

        return SearchQuery::query()
            ->select('device', DB::raw('SUM(impressions) as total_impressions'), DB::raw('SUM(clicks) as total_clicks'), DB::raw('AVG(ctr) as avg_ctr'))
            ->where('date', '>=', $thirtyDaysAgo)
            ->groupBy('device')
            ->orderByDesc('total_clicks')
            ->get()
            ->map(function ($item, $index) {
                return [
                    'id' => $index + 1,
                    'device' => $this->translateDevice($item->device),
                    'impressions' => (int) $item->total_impressions,
                    'clicks' => (int) $item->total_clicks,
                    'ctr' => round((float) $item->avg_ctr, 2),
                ];
            })
            ->toArray();
    }

    protected function translateDevice(string $device): string
    {
        return match (mb_strtolower($device)) {
            'desktop' => 'Asztali gép',
            'mobile' => 'Mobil',
            'tablet' => 'Táblagép',
            default => ucfirst($device),
        };
    }
}
