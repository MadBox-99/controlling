<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\KpiCategory;
use App\Enums\KpiDataSource;
use App\Enums\KpiFormat;
use App\Models\Kpi;
use Illuminate\Database\Seeder;

final class AnalyticsKpiSeeder extends Seeder
{
    public function run(): void
    {
        $analyticsKpis = [
            [
                'code' => 'analytics_sessions',
                'name' => 'Total Sessions',
                'description' => 'Total number of sessions from Google Analytics',
                'data_source' => KpiDataSource::Analytics,
                'category' => KpiCategory::Traffic,
                'format' => KpiFormat::Number,
                'target_value' => null,
                'is_active' => true,
            ],
            [
                'code' => 'analytics_users',
                'name' => 'Total Users',
                'description' => 'Total number of users from Google Analytics',
                'data_source' => KpiDataSource::Analytics,
                'category' => KpiCategory::Traffic,
                'format' => KpiFormat::Number,
                'target_value' => null,
                'is_active' => true,
            ],
            [
                'code' => 'analytics_pageviews',
                'name' => 'Total Pageviews',
                'description' => 'Total number of pageviews from Google Analytics',
                'data_source' => KpiDataSource::Analytics,
                'category' => KpiCategory::Traffic,
                'format' => KpiFormat::Number,
                'target_value' => null,
                'is_active' => true,
            ],
            [
                'code' => 'analytics_bounce_rate',
                'name' => 'Bounce Rate',
                'description' => 'Average bounce rate from Google Analytics',
                'data_source' => KpiDataSource::Analytics,
                'category' => KpiCategory::Engagement,
                'format' => KpiFormat::Percentage,
                'target_value' => 40.00,
                'is_active' => true,
            ],
            [
                'code' => 'analytics_avg_session_duration',
                'name' => 'Avg Session Duration',
                'description' => 'Average session duration in seconds from Google Analytics',
                'data_source' => KpiDataSource::Analytics,
                'category' => KpiCategory::Engagement,
                'format' => KpiFormat::Number,
                'target_value' => 180.00,
                'is_active' => true,
            ],
            [
                'code' => 'analytics_conversion_rate',
                'name' => 'Conversion Rate',
                'description' => 'Overall conversion rate from Google Analytics',
                'data_source' => KpiDataSource::Analytics,
                'category' => KpiCategory::Conversion,
                'format' => KpiFormat::Percentage,
                'target_value' => 2.00,
                'is_active' => true,
            ],
            [
                'code' => 'analytics_conversions',
                'name' => 'Total Conversions',
                'description' => 'Total number of conversions from Google Analytics',
                'data_source' => KpiDataSource::Analytics,
                'category' => KpiCategory::Conversion,
                'format' => KpiFormat::Number,
                'target_value' => null,
                'is_active' => true,
            ],
            [
                'code' => 'analytics_events',
                'name' => 'Total Events',
                'description' => 'Total number of events triggered from Google Analytics',
                'data_source' => KpiDataSource::Analytics,
                'category' => KpiCategory::Engagement,
                'format' => KpiFormat::Number,
                'target_value' => null,
                'is_active' => true,
            ],
        ];

        foreach ($analyticsKpis as $kpiData) {
            Kpi::updateOrCreate(
                ['code' => $kpiData['code']],
                $kpiData,
            );
        }
    }
}
