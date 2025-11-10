<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\AnalyticsConversion;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Models\Kpi;
use App\Models\KpiValue;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

final class SyncAnalyticsKpis implements ShouldQueue
{
    use Batchable;
    use Queueable;

    public function handle(): void
    {
        // Check if Analytics KPIs exist, if not create them
        $kpisCount = Kpi::query()
            ->where('data_source', 'analytics')
            ->count();

        if ($kpisCount === 0) {
            $this->createDefaultAnalyticsKpis();
        }

        // Get all Analytics KPIs
        $kpis = Kpi::query()
            ->where('data_source', 'analytics')
            ->where('is_active', true)
            ->get();

        foreach ($kpis as $kpi) {
            match ($kpi->code) {
                'analytics_sessions' => $this->syncSessions($kpi),
                'analytics_users' => $this->syncUsers($kpi),
                'analytics_pageviews' => $this->syncPageviews($kpi),
                'analytics_bounce_rate' => $this->syncBounceRate($kpi),
                'analytics_avg_session_duration' => $this->syncAvgSessionDuration($kpi),
                'analytics_conversion_rate' => $this->syncConversionRate($kpi),
                'analytics_conversions' => $this->syncConversions($kpi),
                'analytics_events' => $this->syncEvents($kpi),
                default => null,
            };
        }
    }

    private function createDefaultAnalyticsKpis(): void
    {
        $analyticsKpis = [
            [
                'code' => 'analytics_sessions',
                'name' => 'Total Sessions',
                'description' => 'Total number of sessions from Google Analytics',
                'data_source' => 'analytics',
                'category' => 'traffic',
                'format' => 'number',
                'target_value' => null,
                'is_active' => true,
            ],
            [
                'code' => 'analytics_users',
                'name' => 'Total Users',
                'description' => 'Total number of users from Google Analytics',
                'data_source' => 'analytics',
                'category' => 'traffic',
                'format' => 'number',
                'target_value' => null,
                'is_active' => true,
            ],
            [
                'code' => 'analytics_pageviews',
                'name' => 'Total Pageviews',
                'description' => 'Total number of pageviews from Google Analytics',
                'data_source' => 'analytics',
                'category' => 'traffic',
                'format' => 'number',
                'target_value' => null,
                'is_active' => true,
            ],
            [
                'code' => 'analytics_bounce_rate',
                'name' => 'Bounce Rate',
                'description' => 'Average bounce rate from Google Analytics',
                'data_source' => 'analytics',
                'category' => 'engagement',
                'format' => 'percentage',
                'target_value' => 40.00,
                'is_active' => true,
            ],
            [
                'code' => 'analytics_avg_session_duration',
                'name' => 'Avg Session Duration',
                'description' => 'Average session duration in seconds from Google Analytics',
                'data_source' => 'analytics',
                'category' => 'engagement',
                'format' => 'number',
                'target_value' => 180.00,
                'is_active' => true,
            ],
            [
                'code' => 'analytics_conversion_rate',
                'name' => 'Conversion Rate',
                'description' => 'Overall conversion rate from Google Analytics',
                'data_source' => 'analytics',
                'category' => 'conversion',
                'format' => 'percentage',
                'target_value' => 2.00,
                'is_active' => true,
            ],
            [
                'code' => 'analytics_conversions',
                'name' => 'Total Conversions',
                'description' => 'Total number of conversions from Google Analytics',
                'data_source' => 'analytics',
                'category' => 'conversion',
                'format' => 'number',
                'target_value' => null,
                'is_active' => true,
            ],
            [
                'code' => 'analytics_events',
                'name' => 'Total Events',
                'description' => 'Total number of events triggered from Google Analytics',
                'data_source' => 'analytics',
                'category' => 'engagement',
                'format' => 'number',
                'target_value' => null,
                'is_active' => true,
            ],
        ];

        foreach ($analyticsKpis as $kpiData) {
            Kpi::create($kpiData);
        }
    }

    private function syncSessions(Kpi $kpi): void
    {
        $sessions = AnalyticsSession::query()
            ->select(DB::raw('DATE(date) as period'), DB::raw('SUM(sessions) as total'))
            ->groupBy('period')
            ->get();

        foreach ($sessions as $session) {
            $this->upsertKpiValue($kpi, $session->period, $session->total);
        }
    }

    private function syncUsers(Kpi $kpi): void
    {
        $users = AnalyticsSession::query()
            ->select(DB::raw('DATE(date) as period'), DB::raw('SUM(users) as total'))
            ->groupBy('period')
            ->get();

        foreach ($users as $user) {
            $this->upsertKpiValue($kpi, $user->period, $user->total);
        }
    }

    private function syncPageviews(Kpi $kpi): void
    {
        $pageviews = AnalyticsPageview::query()
            ->select(DB::raw('DATE(date) as period'), DB::raw('SUM(pageviews) as total'))
            ->groupBy('period')
            ->get();

        foreach ($pageviews as $pageview) {
            $this->upsertKpiValue($kpi, $pageview->period, $pageview->total);
        }
    }

    private function syncBounceRate(Kpi $kpi): void
    {
        $bounceRates = AnalyticsSession::query()
            ->select(DB::raw('DATE(date) as period'), DB::raw('AVG(bounce_rate) as average'))
            ->groupBy('period')
            ->get();

        foreach ($bounceRates as $bounceRate) {
            $this->upsertKpiValue($kpi, $bounceRate->period, $bounceRate->average);
        }
    }

    private function syncAvgSessionDuration(Kpi $kpi): void
    {
        $durations = AnalyticsSession::query()
            ->select(DB::raw('DATE(date) as period'), DB::raw('AVG(avg_session_duration) as average'))
            ->groupBy('period')
            ->get();

        foreach ($durations as $duration) {
            $this->upsertKpiValue($kpi, $duration->period, $duration->average);
        }
    }

    private function syncConversionRate(Kpi $kpi): void
    {
        $conversionRates = AnalyticsConversion::query()
            ->select(DB::raw('DATE(date) as period'), DB::raw('AVG(conversion_rate) as average'))
            ->groupBy('period')
            ->get();

        foreach ($conversionRates as $conversionRate) {
            $this->upsertKpiValue($kpi, $conversionRate->period, $conversionRate->average);
        }
    }

    private function syncConversions(Kpi $kpi): void
    {
        $conversions = AnalyticsConversion::query()
            ->select(DB::raw('DATE(date) as period'), DB::raw('SUM(goal_completions) as total'))
            ->groupBy('period')
            ->get();

        foreach ($conversions as $conversion) {
            $this->upsertKpiValue($kpi, $conversion->period, $conversion->total);
        }
    }

    private function syncEvents(Kpi $kpi): void
    {
        $events = AnalyticsEvent::query()
            ->select(DB::raw('DATE(date) as period'), DB::raw('SUM(event_count) as total'))
            ->groupBy('period')
            ->get();

        foreach ($events as $event) {
            $this->upsertKpiValue($kpi, $event->period, $event->total);
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
