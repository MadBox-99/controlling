<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kpis\Schemas;

use App\Enums\KpiCategory;
use App\Enums\KpiDataSource;
use App\Enums\KpiGoalType;
use App\Enums\KpiValueType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class KpiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->required(),
                                TextInput::make('name')
                                    ->required(),
                                Select::make('data_source')
                                    ->options(KpiDataSource::class)
                                    ->required(),
                                Select::make('category')
                                    ->options(KpiCategory::class)
                                    ->required(),
                                Toggle::make('is_active')
                                    ->required(),
                            ]),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ]),
                Section::make('Target & Goal Settings')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('target_value')
                                    ->numeric()
                                    ->label('Target Value'),
                                Select::make('goal_type')
                                    ->label('Goal Type')
                                    ->options(KpiGoalType::class)
                                    ->native(false),
                                Select::make('value_type')
                                    ->label('Value Type')
                                    ->options(KpiValueType::class)
                                    ->native(false),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('from_date')
                                    ->label('Start Date')
                                    ->native(false)
                                    ->displayFormat('Y-m-d')
                                    ->maxDate(fn ($get) => $get('target_date'))
                                    ->helperText('When to start tracking this KPI'),
                                DatePicker::make('target_date')
                                    ->label('Target Date')
                                    ->native(false)
                                    ->displayFormat('Y-m-d')
                                    ->minDate(fn ($get) => $get('from_date'))
                                    ->helperText('When you want to achieve the target'),
                            ]),
                    ]),
                Section::make('Analytics Integration')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('page_path')
                                    ->label('Page Path')
                                    ->helperText('Analytics page path being tracked'),
                                TextInput::make('metric_type')
                                    ->label('Metric Type')
                                    ->helperText('Analytics metric type (e.g., pageviews, bounce_rate)'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
