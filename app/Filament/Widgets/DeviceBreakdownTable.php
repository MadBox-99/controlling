<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Support\DeviceBreakdownModel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

final class DeviceBreakdownTable extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected $listeners = ['dateRangeUpdated' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => DeviceBreakdownModel::query())
            ->columns([
                TextColumn::make('device')
                    ->label('Device')
                    ->weight('bold'),

                TextColumn::make('impressions')
                    ->label('Impressions')
                    ->sortable()
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('clicks')
                    ->label('Clicks')
                    ->sortable()
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('ctr')
                    ->label('CTR')
                    ->sortable()
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%')
                    ->alignEnd(),
            ])
            ->defaultSort('clicks', 'desc')
            ->paginated(false);
    }

    protected function getTableHeading(): string
    {
        return sprintf('Eszközök (%s)', $this->getDateRangeLabel());
    }

    private function getDateRangeLabel(): string
    {
        $dateRangeType = session('search_console_date_range', '28_days');

        return match ($dateRangeType) {
            '24_hours' => 'Elmúlt 24 óra',
            '7_days' => 'Elmúlt 7 nap',
            '28_days' => 'Elmúlt 28 nap',
            '3_months' => 'Elmúlt 3 hónap',
            default => 'Elmúlt 28 nap',
        };
    }
}
