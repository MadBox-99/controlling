<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Settings;
use Exception;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Google\Client;
use Google\Service\AnalyticsData;
use Google\Service\AnalyticsData\DateRange;
use Google\Service\AnalyticsData\Metric;
use Google\Service\AnalyticsData\RunReportRequest;
use Illuminate\Support\Facades\Storage;

final class GeneralStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $stats = $this->loadStats();

        return [
            Stat::make('Aktív felhasználók', number_format($stats['active_users']))
                ->description('Elmúlt 30 nap')
                ->color('success'),

            Stat::make('Új felhasználók', number_format($stats['new_users']))
                ->description('Elmúlt 30 nap')
                ->color('info'),

            Stat::make('Tevékenység átlagos időtartama', $this->formatDuration($stats['avg_session_duration']))
                ->description('Átlagos munkamenet')
                ->color('warning'),

            Stat::make('Eseményszám', $this->formatNumber($stats['event_count']))
                ->description('Összes esemény')
                ->color('primary'),
        ];
    }

    private function loadStats(): array
    {
        try {
            $settings = Settings::query()->first();

            if (! $settings || ! $settings->google_service_account || ! $settings->property_id) {
                return $this->getEmptyStats();
            }

            $client = new Client();
            $client->useApplicationDefaultCredentials();
            $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
            $client->setAuthConfig(Storage::json($settings->google_service_account));

            $service = new AnalyticsData($client);

            $dateRange = new DateRange();
            $dateRange->setStartDate('30daysAgo');
            $dateRange->setEndDate('today');

            $request = new RunReportRequest();
            $request->setDateRanges([$dateRange]);
            $request->setMetrics([
                (new Metric())->setName('activeUsers'),
                (new Metric())->setName('newUsers'),
                (new Metric())->setName('averageSessionDuration'),
                (new Metric())->setName('eventCount'),
            ]);

            $response = $service->properties->runReport(
                property: 'properties/' . $settings->property_id,
                postBody: $request,
            );

            if ($response->getRows() && count($response->getRows()) > 0) {
                $row = $response->getRows()[0];

                return [
                    'active_users' => (int) $row->getMetricValues()[0]->getValue(),
                    'new_users' => (int) $row->getMetricValues()[1]->getValue(),
                    'avg_session_duration' => (float) $row->getMetricValues()[2]->getValue(),
                    'event_count' => (int) $row->getMetricValues()[3]->getValue(),
                ];
            }

            return $this->getEmptyStats();
        } catch (Exception) {
            return $this->getEmptyStats();
        }
    }

    private function getEmptyStats(): array
    {
        return [
            'active_users' => 0,
            'new_users' => 0,
            'avg_session_duration' => 0,
            'event_count' => 0,
        ];
    }

    private function formatDuration(float $seconds): string
    {
        if ($seconds < 60) {
            return round($seconds) . ' mp';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = round($seconds % 60);

        if ($minutes < 60) {
            return $minutes . ' p ' . ($remainingSeconds > 0 ? $remainingSeconds . ' mp' : '');
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return $hours . ' ó ' . ($remainingMinutes > 0 ? $remainingMinutes . ' p' : '');
    }

    private function formatNumber(int $number): string
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . ' M';
        }

        if ($number >= 1000) {
            return round($number / 1000, 1) . ' E';
        }

        return (string) $number;
    }
}
