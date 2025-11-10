<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Kpi;
use App\Models\KpiValue;
use App\Models\SearchQuery;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

final class SyncSearchConsoleKpis implements ShouldQueue
{
    use Batchable;
    use Queueable;

    public function handle(): void
    {
        // Check if Search Console KPIs exist, if not create them
        $kpisCount = Kpi::query()
            ->where('data_source', 'search_console')
            ->count();

        if ($kpisCount === 0) {
            $this->createDefaultSearchConsoleKpis();
        }

        // Get all Search Console KPIs
        $kpis = Kpi::query()
            ->where('data_source', 'search_console')
            ->where('is_active', true)
            ->get();

        foreach ($kpis as $kpi) {
            match ($kpi->code) {
                'search_console_impressions' => $this->syncImpressions($kpi),
                'search_console_clicks' => $this->syncClicks($kpi),
                'search_console_ctr' => $this->syncCtr($kpi),
                'search_console_position' => $this->syncPosition($kpi),
                default => null,
            };
        }
    }

    private function createDefaultSearchConsoleKpis(): void
    {
        $searchConsoleKpis = [
            [
                'code' => 'search_console_impressions',
                'name' => 'Total Impressions',
                'description' => 'Total number of impressions from Google Search Console',
                'data_source' => 'search_console',
                'category' => 'seo',
                'format' => 'number',
                'target_value' => null,
                'is_active' => true,
            ],
            [
                'code' => 'search_console_clicks',
                'name' => 'Total Clicks',
                'description' => 'Total number of clicks from Google Search Console',
                'data_source' => 'search_console',
                'category' => 'seo',
                'format' => 'number',
                'target_value' => null,
                'is_active' => true,
            ],
            [
                'code' => 'search_console_ctr',
                'name' => 'Click-Through Rate',
                'description' => 'Average click-through rate from Google Search Console',
                'data_source' => 'search_console',
                'category' => 'seo',
                'format' => 'percentage',
                'target_value' => 5.00,
                'is_active' => true,
            ],
            [
                'code' => 'search_console_position',
                'name' => 'Average Position',
                'description' => 'Average search position from Google Search Console',
                'data_source' => 'search_console',
                'category' => 'seo',
                'format' => 'number',
                'target_value' => 10.00,
                'is_active' => true,
            ],
        ];

        foreach ($searchConsoleKpis as $kpiData) {
            Kpi::create($kpiData);
        }
    }

    private function syncImpressions(Kpi $kpi): void
    {
        $impressions = SearchQuery::query()
            ->select(DB::raw('DATE(date) as period'), DB::raw('SUM(impressions) as total'))
            ->groupBy('period')
            ->get();

        foreach ($impressions as $impression) {
            $this->upsertKpiValue($kpi, $impression->period, $impression->total);
        }
    }

    private function syncClicks(Kpi $kpi): void
    {
        $clicks = SearchQuery::query()
            ->select(DB::raw('DATE(date) as period'), DB::raw('SUM(clicks) as total'))
            ->groupBy('period')
            ->get();

        foreach ($clicks as $click) {
            $this->upsertKpiValue($kpi, $click->period, $click->total);
        }
    }

    private function syncCtr(Kpi $kpi): void
    {
        $ctrs = SearchQuery::query()
            ->select(DB::raw('DATE(date) as period'), DB::raw('AVG(ctr) as average'))
            ->groupBy('period')
            ->get();

        foreach ($ctrs as $ctr) {
            $this->upsertKpiValue($kpi, $ctr->period, $ctr->average);
        }
    }

    private function syncPosition(Kpi $kpi): void
    {
        $positions = SearchQuery::query()
            ->select(DB::raw('DATE(date) as period'), DB::raw('AVG(position) as average'))
            ->groupBy('period')
            ->get();

        foreach ($positions as $position) {
            $this->upsertKpiValue($kpi, $position->period, $position->average);
        }
    }

    private function upsertKpiValue(Kpi $kpi, string $period, float|int|string $actualValue): void
    {
        $kpiValue = KpiValue::query()
            ->where('kpi_id', $kpi->id)
            ->whereDate('period', $period)
            ->first();

        if ($kpiValue) {
            $kpiValue->update([
                'actual_value' => $actualValue,
            ]);
            $kpiValue->calculateVariance();
            $kpiValue->save();
        } else {
            $kpiValue = KpiValue::create([
                'kpi_id' => $kpi->id,
                'period' => $period,
                'actual_value' => $actualValue,
                'planned_value' => $kpi->target_value,
            ]);
            $kpiValue->calculateVariance();
            $kpiValue->save();
        }
    }
}
