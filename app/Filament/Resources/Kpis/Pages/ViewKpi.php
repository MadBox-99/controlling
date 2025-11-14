<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kpis\Pages;

use App\Filament\Resources\Kpis\KpiResource;
use App\Filament\Resources\Kpis\Widgets\KpiProgressWidget;
use App\Filament\Resources\Kpis\Widgets\KpiValuesChartWidget;
use App\Models\Kpi;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ViewKpi extends ViewRecord
{
    protected static string $resource = KpiResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $kpi = $this->record;

        if (! $kpi instanceof Kpi) {
            return;
        }

        $missingFields = [];

        if (! $kpi->from_date) {
            $missingFields[] = 'Start Date';
        }

        if (! $kpi->target_date) {
            $missingFields[] = 'Target Date';
        }

        if (! empty($missingFields)) {
            Notification::make()
                ->warning()
                ->title('Missing Configuration')
                ->body('Missing required field(s): ' . implode(', ', $missingFields) . '. Please set them to view the chart.')
                ->persistent()
                ->actions([
                    Action::make('edit')
                        ->label('Edit KPI')
                        ->button()
                        ->url($this->getResource()::getUrl('edit', ['record' => $kpi]))
                        ->close(),
                ])
                ->send();
        }
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('KPI Overview')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Name'),
                                TextEntry::make('code')
                                    ->label('Code'),
                                TextEntry::make('data_source')
                                    ->badge()
                                    ->label('Data Source'),
                                TextEntry::make('category')
                                    ->badge()
                                    ->label('Category'),
                                TextEntry::make('is_active')
                                    ->label('Status')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                            ]),
                        TextEntry::make('description')
                            ->columnSpanFull(),
                    ]),
                Section::make('Goal Settings')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('target_value')
                                    ->label('Target Value')
                                    ->numeric(decimalPlaces: 2),
                                TextEntry::make('goal_type')
                                    ->badge()
                                    ->label('Goal Type'),
                                TextEntry::make('value_type')
                                    ->badge()
                                    ->label('Value Type'),
                                TextEntry::make('from_date')
                                    ->label('Start Date')
                                    ->date()
                                    ->visible(fn (Kpi $record): bool => $record->from_date !== null),
                                TextEntry::make('target_date')
                                    ->label('Target Date')
                                    ->date(),
                                TextEntry::make('page_path')
                                    ->label('Page Path')
                                    ->visible(fn (Kpi $record): bool => $record->page_path !== null),
                                TextEntry::make('metric_type')
                                    ->badge()
                                    ->label('Metric')
                                    ->visible(fn (Kpi $record): bool => $record->metric_type !== null),
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            KpiProgressWidget::class,
            KpiValuesChartWidget::class,
        ];
    }
}
