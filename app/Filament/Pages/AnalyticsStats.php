<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\AnalyticsSortEnum;
use App\Enums\KpiGoalType;
use App\Enums\KpiValueType;
use App\Enums\NavigationGroup;
use App\Filament\Resources\Kpis\KpiResource;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Models\Kpi;
use App\Models\Settings;
use Exception;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Google\Client;
use Google\Service\AnalyticsData;
use Google\Service\AnalyticsData\DateRange;
use Google\Service\AnalyticsData\Dimension;
use Google\Service\AnalyticsData\Metric;
use Google\Service\AnalyticsData\RunReportRequest;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

final class AnalyticsStats extends Page
{
    public array $topPages = [];

    public array $userSources = [];

    public array $stats = [];

    protected string $view = 'filament.pages.analytics-stats';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Analytics;

    protected static ?int $navigationSort = AnalyticsSortEnum::AnalyticsStats->value;

    protected static ?string $navigationLabel = 'Analytics Statistics';

    protected static ?string $title = 'Analytics Statistics';

    public function mount(): void
    {
        $this->loadAnalyticsData();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('setKpiGoal')
                ->label('Set KPI Goal')
                ->slideOver()
                ->stickyModalFooter()
                ->schema(function (): array {
                    $pageOptions = collect($this->topPages)->mapWithKeys(function (array $page): array {
                        return [$page['page_path'] => "{$page['page_title']} ({$page['page_path']})"];
                    })->toArray();

                    return [
                        Select::make('page_path')
                            ->label('Select Analytics Page')
                            ->options($pageOptions)
                            ->required()
                            ->searchable()
                            ->helperText('Choose a page from your Google Analytics data'),
                        Select::make('metric_type')
                            ->label('Select Metric')
                            ->options([
                                'pageviews' => 'Pageviews',
                                'unique_pageviews' => 'Unique Pageviews',
                                'bounce_rate' => 'Bounce Rate',
                            ])
                            ->required()
                            ->native(false)
                            ->helperText('Choose which metric to track'),
                        DatePicker::make('from_date')
                            ->label('Start Date')
                            ->required()
                            ->native(false)
                            ->displayFormat('Y-m-d')
                            ->default(now())
                            ->maxDate(fn ($get) => $get('target_date'))
                            ->helperText('When to start tracking this KPI'),
                        DatePicker::make('target_date')
                            ->label('Target Date')
                            ->required()
                            ->native(false)
                            ->displayFormat('Y-m-d')
                            ->minDate(fn ($get) => $get('from_date') ?? now())
                            ->helperText('When you want to achieve the target'),
                        Select::make('goal_type')
                            ->label('Goal Type')
                            ->options([
                                KpiGoalType::Increase->value => 'Increase',
                                KpiGoalType::Decrease->value => 'Decrease',
                            ])
                            ->required()
                            ->native(false),
                        Select::make('value_type')
                            ->label('Value Type')
                            ->options([
                                KpiValueType::Percentage->value => 'Percentage (%)',
                                KpiValueType::Fixed->value => 'Fixed Number',
                            ])
                            ->required()
                            ->native(false)
                            ->live(),
                        TextInput::make('target_value')
                            ->label(fn ($get) => $get('value_type') === KpiValueType::Percentage->value ? 'Target Percentage (%)' : 'Target Value')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix(fn ($get) => $get('value_type') === KpiValueType::Percentage->value ? '%' : null),
                    ];
                })
                ->action(function (array $data): void {
                    $pagePath = $data['page_path'];
                    $metricType = $data['metric_type'];
                    $kpiCode = 'analytics_' . str_replace(['/', ' '], ['_', '_'], $pagePath) . '_' . $metricType;

                    $pageData = collect($this->topPages)->firstWhere('page_path', $pagePath);
                    $pageTitle = $pageData['page_title'] ?? $pagePath;

                    $kpi = Kpi::query()->updateOrCreate(
                        [
                            'code' => $kpiCode,
                            'team_id' => Filament::getTenant()->id,
                        ],
                        [
                            'name' => "{$pageTitle} - {$metricType}",
                            'description' => "Track {$metricType} for {$pagePath}",
                            'data_source' => 'analytics',
                            'category' => 'traffic',
                            'page_path' => $pagePath,
                            'metric_type' => $metricType,
                            'from_date' => $data['from_date'] ?? now(),
                            'target_date' => $data['target_date'],
                            'goal_type' => $data['goal_type'],
                            'value_type' => $data['value_type'],
                            'target_value' => $data['target_value'],
                            'is_active' => true,
                        ],
                    );

                    Notification::make()
                        ->title('KPI Goal Set Successfully')
                        ->success()
                        ->body("Your goal for **{$kpi->name}** has been saved.")
                        ->actions([
                            Action::make('view')
                                ->label('View KPI')
                                ->url(KpiResource::getUrl('view', ['record' => $kpi->getRouteKey(), 'tenant' => Filament::getTenant()])),
                        ])
                        ->send();
                })
                ->closeModalByClickingAway(false),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // SourcePageBreakdown::class,
        ];
    }

    private function loadAnalyticsData(): void
    {
        // Load top pages from database
        $topPagesFromDb = AnalyticsPageview::query()
            ->orderBy('pageviews', 'desc')
            ->limit(10)
            ->get();

        if ($topPagesFromDb->isNotEmpty()) {
            $this->topPages = $topPagesFromDb
                ->map(fn (AnalyticsPageview $page): array => [
                    'page_path' => $page->page_path,
                    'page_title' => $page->page_title,
                    'pageviews' => $page->pageviews,
                    'unique_pageviews' => $page->unique_pageviews,
                    'bounce_rate' => $page->bounce_rate,
                ])
                ->toArray();
        } else {
            // Load from Google Analytics API if no database data
            $this->loadTopPagesFromApi();
        }

        // Load user sources from Google Analytics API
        $this->loadUserSources();

        // Load overall stats
        $this->stats = [
            'total_pageviews' => AnalyticsPageview::query()->sum('pageviews') ?: 0,
            'total_sessions' => AnalyticsSession::query()->count() ?: 0,
            'avg_bounce_rate' => AnalyticsPageview::query()->avg('bounce_rate') ?: 0,
        ];
    }

    private function loadTopPagesFromApi(): void
    {
        try {
            $settings = Settings::query()->first();

            if (! $settings || ! $settings->google_service_account || ! $settings->property_id) {
                $this->topPages = [];

                return;
            }

            $client = new Client();
            $client->useApplicationDefaultCredentials();
            $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
            $client->setAuthConfig(Storage::json($settings->google_service_account));

            $service = new AnalyticsData($client);

            $dateRange = new DateRange();
            $dateRange->setStartDate('30daysAgo');
            $dateRange->setEndDate('today');

            $pagePathDimension = new Dimension();
            $pagePathDimension->setName('pagePath');

            $pageTitleDimension = new Dimension();
            $pageTitleDimension->setName('pageTitle');

            $pageviewsMetric = new Metric();
            $pageviewsMetric->setName('screenPageViews');

            $uniquePageviewsMetric = new Metric();
            $uniquePageviewsMetric->setName('sessions');

            $bounceRateMetric = new Metric();
            $bounceRateMetric->setName('bounceRate');

            $request = new RunReportRequest();
            $request->setDateRanges([$dateRange]);
            $request->setDimensions([$pagePathDimension, $pageTitleDimension]);
            $request->setMetrics([$pageviewsMetric, $uniquePageviewsMetric, $bounceRateMetric]);

            $response = $service->properties->runReport(
                property: 'properties/' . $settings->property_id,
                postBody: $request,
            );

            $pages = [];

            foreach ($response->getRows() as $row) {
                $pagePath = $row->getDimensionValues()[0]->getValue();
                $pageTitle = $row->getDimensionValues()[1]->getValue();
                $pageviews = (int) $row->getMetricValues()[0]->getValue();
                $uniquePageviews = (int) $row->getMetricValues()[1]->getValue();
                $bounceRate = (float) $row->getMetricValues()[2]->getValue() * 100;

                $pages[] = [
                    'page_path' => $pagePath,
                    'page_title' => $pageTitle,
                    'pageviews' => $pageviews,
                    'unique_pageviews' => $uniquePageviews,
                    'bounce_rate' => $bounceRate,
                ];
            }

            // Sort by pageviews descending and take top 10
            usort($pages, fn (array $a, array $b): int => $b['pageviews'] <=> $a['pageviews']);
            $this->topPages = array_slice($pages, 0, 10);
        } catch (Exception) {
            $this->topPages = [];
        }
    }

    private function loadUserSources(): void
    {
        try {
            $settings = Settings::query()->first();

            if (! $settings || ! $settings->google_service_account || ! $settings->property_id) {
                $this->userSources = [];

                return;
            }

            $client = new Client();
            $client->useApplicationDefaultCredentials();
            $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
            $client->setAuthConfig(Storage::json($settings->google_service_account));

            $service = new AnalyticsData($client);

            $dateRange = new DateRange();
            $dateRange->setStartDate('30daysAgo');
            $dateRange->setEndDate('today');

            $sourceDimension = new Dimension();
            $sourceDimension->setName('sessionSource');

            $mediumDimension = new Dimension();
            $mediumDimension->setName('sessionMedium');

            $sessionsMetric = new Metric();
            $sessionsMetric->setName('sessions');

            $usersMetric = new Metric();
            $usersMetric->setName('activeUsers');

            $request = new RunReportRequest();
            $request->setDateRanges([$dateRange]);
            $request->setDimensions([$sourceDimension, $mediumDimension]);
            $request->setMetrics([$sessionsMetric, $usersMetric]);

            $response = $service->properties->runReport(
                property: 'properties/' . $settings->property_id,
                postBody: $request,
            );

            $sources = [];

            foreach ($response->getRows() as $row) {
                $source = $row->getDimensionValues()[0]->getValue();
                $medium = $row->getDimensionValues()[1]->getValue();
                $sessions = (int) $row->getMetricValues()[0]->getValue();
                $users = (int) $row->getMetricValues()[1]->getValue();

                $sources[] = [
                    'source' => $source,
                    'medium' => $medium,
                    'sessions' => $sessions,
                    'users' => $users,
                ];
            }

            // Sort by sessions descending and take top 10
            usort($sources, fn (array $a, array $b): int => $b['sessions'] <=> $a['sessions']);
            $this->userSources = array_slice($sources, 0, 10);
        } catch (Exception) {
            $this->userSources = [];
        }
    }
}
