<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\AnalyticsSortEnum;
use App\Enums\NavigationGroup;
use App\Models\SearchPage;
use App\Models\SearchQuery;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use UnitEnum;

final class SearchConsoleGeneralStats extends Page
{
    public array $stats = [];

    public array $topQueries = [];

    public array $topPages = [];

    public array $deviceBreakdown = [];

    protected string $view = 'filament.pages.search-console-general-stats';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::SearchConsole;

    protected static ?int $navigationSort = AnalyticsSortEnum::SearchConsoleGeneralStats->value;

    protected static ?string $navigationLabel = 'General Search Console';

    protected static ?string $title = 'General Search Console Dashboard';

    public function mount(): void
    {
        $this->loadSearchConsoleData();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\SearchConsoleStatsOverview::class,
        ];
    }

    protected function loadSearchConsoleData(): void
    {
        $thirtyDaysAgo = now()->subDays(30);

        // Load general stats from last 30 days
        $this->stats = [
            'total_impressions' => SearchQuery::where('date', '>=', $thirtyDaysAgo)->sum('impressions'),
            'total_clicks' => SearchQuery::where('date', '>=', $thirtyDaysAgo)->sum('clicks'),
            'avg_ctr' => SearchQuery::where('date', '>=', $thirtyDaysAgo)->avg('ctr') ?? 0,
            'avg_position' => SearchQuery::where('date', '>=', $thirtyDaysAgo)->avg('position') ?? 0,
        ];

        // Load top queries
        $this->topQueries = SearchQuery::query()
            ->select('query', DB::raw('SUM(impressions) as total_impressions'), DB::raw('SUM(clicks) as total_clicks'), DB::raw('AVG(ctr) as avg_ctr'), DB::raw('AVG(position) as avg_position'))
            ->where('date', '>=', $thirtyDaysAgo)
            ->groupBy('query')
            ->orderByDesc('total_clicks')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'query' => $item->query,
                'impressions' => (int) $item->total_impressions,
                'clicks' => (int) $item->total_clicks,
                'ctr' => round((float) $item->avg_ctr, 2),
                'position' => round((float) $item->avg_position, 2),
            ])
            ->toArray();

        // Load top pages
        $this->topPages = SearchPage::query()
            ->select('page_url', DB::raw('SUM(impressions) as total_impressions'), DB::raw('SUM(clicks) as total_clicks'), DB::raw('AVG(ctr) as avg_ctr'), DB::raw('AVG(position) as avg_position'))
            ->where('date', '>=', $thirtyDaysAgo)
            ->groupBy('page_url')
            ->orderByDesc('total_clicks')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'page_url' => $item->page_url,
                'impressions' => (int) $item->total_impressions,
                'clicks' => (int) $item->total_clicks,
                'ctr' => round((float) $item->avg_ctr, 2),
                'position' => round((float) $item->avg_position, 2),
            ])
            ->toArray();

        // Load device breakdown
        $this->deviceBreakdown = SearchQuery::query()
            ->select('device', DB::raw('SUM(impressions) as total_impressions'), DB::raw('SUM(clicks) as total_clicks'))
            ->where('date', '>=', $thirtyDaysAgo)
            ->groupBy('device')
            ->get()
            ->map(fn ($item) => [
                'device' => $item->device,
                'impressions' => (int) $item->total_impressions,
                'clicks' => (int) $item->total_clicks,
            ])
            ->toArray();
    }
}
