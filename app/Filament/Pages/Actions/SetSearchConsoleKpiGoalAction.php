<?php

declare(strict_types=1);

namespace App\Filament\Pages\Actions;

use App\Enums\KpiGoalType;
use App\Enums\KpiValueType;
use App\Filament\Resources\Kpis\KpiResource;
use App\Models\Kpi;
use Closure;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

final class SetSearchConsoleKpiGoalAction
{
    public static function make(Closure $getTopPages, Closure $getTopQueries): Action
    {
        return Action::make('setKpiGoal')
            ->label('Set KPI Goal')
            ->slideOver()
            ->stickyModalFooter()
            ->schema(function () use ($getTopPages, $getTopQueries): array {
                $topPages = $getTopPages();
                $topQueries = $getTopQueries();

                $pageOptions = collect($topPages)->mapWithKeys(function (array $page): array {
                    return [$page['page_url'] => "{$page['page_url']} (Clicks: {$page['clicks']})"];
                })->toArray();

                $queryOptions = collect($topQueries)->mapWithKeys(function (array $query): array {
                    return [$query['query'] => "{$query['query']} (Clicks: {$query['clicks']})"];
                })->toArray();

                return [
                    Select::make('source_type')
                        ->label('Source Type')
                        ->options([
                            'page' => 'Search Page',
                            'query' => 'Search Query',
                        ])
                        ->default('page')
                        ->required()
                        ->live()
                        ->helperText('Choose whether to track a page or a search query'),

                    Select::make('page_path')
                        ->label('Select Search Console Page')
                        ->options($pageOptions)
                        ->required(fn ($get) => $get('source_type') === 'page')
                        ->visible(fn ($get) => $get('source_type') === 'page')
                        ->searchable()
                        ->preload()
                        ->helperText('Choose a page from your Search Console data'),

                    Select::make('query')
                        ->label('Select Search Query')
                        ->options($queryOptions)
                        ->required(fn ($get) => $get('source_type') === 'query')
                        ->visible(fn ($get) => $get('source_type') === 'query')
                        ->searchable()
                        ->preload()
                        ->helperText('Choose a search query from your Search Console data'),

                    Select::make('metric_type')
                        ->label('Select Metric')
                        ->options([
                            'impressions' => 'Impressions',
                            'clicks' => 'Clicks',
                            'ctr' => 'CTR (%)',
                            'position' => 'Position',
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
                $sourceType = $data['source_type'];
                $sourceValue = $sourceType === 'page' ? $data['page_path'] : $data['query'];
                $metricType = $data['metric_type'];

                $kpiCode = 'search_console_' . $sourceType . '_' . str_replace(['/', ' ', '.', '?'], ['_', '_', '_', '_'], $sourceValue) . '_' . $metricType;

                // Determine format based on metric type
                $format = match ($metricType) {
                    'ctr' => 'percentage',
                    'impressions', 'clicks', 'position' => 'number',
                    default => 'number',
                };

                $kpi = Kpi::query()->updateOrCreate(
                    [
                        'code' => $kpiCode,
                        'team_id' => Filament::getTenant()->id,
                    ],
                    [
                        'name' => "{$sourceValue} - {$metricType}",
                        'description' => "Track {$metricType} for " . ($sourceType === 'page' ? 'page' : 'query') . " {$sourceValue}",
                        'data_source' => 'search_console',
                        'source_type' => $sourceType,
                        'category' => 'seo',
                        'format' => $format,
                        'page_path' => $sourceValue,
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
            ->closeModalByClickingAway(false);
    }
}
