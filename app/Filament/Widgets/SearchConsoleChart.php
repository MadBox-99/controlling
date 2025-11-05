<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SearchQuery;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class SearchConsoleChart extends ChartWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Teljesítmény (Elmúlt 30 nap)';

    protected function getData(): array
    {
        $thirtyDaysAgo = now()->subDays(30);

        // Get daily aggregated data
        $data = SearchQuery::query()
            ->select(
                DB::raw('DATE(date) as day'),
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('SUM(impressions) as total_impressions')
            )
            ->where('date', '>=', $thirtyDaysAgo)
            ->groupBy('day')
            ->orderBy('day', 'asc')
            ->get();

        $labels = [];
        $clicks = [];
        $impressions = [];

        foreach ($data as $row) {
            $labels[] = date('m. d.', strtotime($row->day));
            $clicks[] = (int) $row->total_clicks;
            $impressions[] = (int) $row->total_impressions;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Kattintások',
                    'data' => $clicks,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Megjelenések',
                    'data' => $impressions,
                    'borderColor' => 'rgb(139, 92, 246)',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Kattintások',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Megjelenések',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }
}
