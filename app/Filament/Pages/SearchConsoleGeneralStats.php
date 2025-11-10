<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\AnalyticsSortEnum;
use App\Enums\NavigationGroup;
use App\Filament\Widgets\SearchConsoleStatsOverview;
use App\Models\SearchPage;
use App\Models\SearchQuery;
use Carbon\CarbonInterface;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use UnitEnum;

final class SearchConsoleGeneralStats extends Page
{
    public string $dateRangeType = '28_days';

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
        $this->dateRangeType = session('search_console_date_range', '28_days');
        $this->loadSearchConsoleData();
    }

    public function setDateRange(string $type): void
    {
        $this->dateRangeType = $type;
        session(['search_console_date_range' => $type]);
        $this->loadSearchConsoleData();
    }

    public function getStartDate(): CarbonInterface
    {
        return match ($this->dateRangeType) {
            '24_hours' => now()->subHours(24),
            '7_days' => now()->subDays(7),
            '28_days' => now()->subDays(28),
            '3_months' => now()->subMonths(3),
            default => now()->subDays(28),
        };
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SearchConsoleStatsOverview::class,
        ];
    }

    private function loadSearchConsoleData(): void
    {
        $startDate = $this->getStartDate();

        // Load general stats
        $this->stats = [
            'total_impressions' => SearchQuery::where('date', '>=', $startDate)->sum('impressions'),
            'total_clicks' => SearchQuery::where('date', '>=', $startDate)->sum('clicks'),
            'avg_ctr' => SearchQuery::where('date', '>=', $startDate)->avg('ctr') ?? 0,
            'avg_position' => SearchQuery::where('date', '>=', $startDate)->avg('position') ?? 0,
        ];

        // Load top queries
        $this->topQueries = SearchQuery::query()
            ->select('query', DB::raw('SUM(impressions) as total_impressions'), DB::raw('SUM(clicks) as total_clicks'), DB::raw('AVG(ctr) as avg_ctr'), DB::raw('AVG(position) as avg_position'))
            ->where('date', '>=', $startDate)
            ->groupBy('query')
            ->orderByDesc('total_clicks')
            ->limit(10)
            ->get()
            ->map(fn ($item): array => [
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
            ->where('date', '>=', $startDate)
            ->groupBy('page_url')
            ->orderByDesc('total_clicks')
            ->limit(10)
            ->get()
            ->map(fn ($item): array => [
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
            ->where('date', '>=', $startDate)
            ->groupBy('device')
            ->get()
            ->map(fn ($item): array => [
                'device' => $item->device,
                'impressions' => (int) $item->total_impressions,
                'clicks' => (int) $item->total_clicks,
            ])
            ->toArray();

        // Dispatch browser event to refresh widgets
        $this->dispatch('dateRangeUpdated', startDate: $startDate->toDateString());
    }
}
