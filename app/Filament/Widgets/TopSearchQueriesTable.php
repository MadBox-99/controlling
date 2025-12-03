<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Support\TopSearchQueryModel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

final class TopSearchQueriesTable extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected $listeners = ['dateRangeUpdated' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => TopSearchQueryModel::query())
            ->columns([
                TextColumn::make('query')
                    ->label('Search Query')
                    ->searchable()
                    ->wrap()
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

                TextColumn::make('position')
                    ->label('Average Position')
                    ->sortable()
                    ->numeric(decimalPlaces: 1)
                    ->alignEnd(),
            ])
            ->defaultSort('clicks', 'desc')
            ->paginated([10, 25, 50]);
    }

    protected function getTableHeading(): string
    {
        return sprintf('Top keresési kifejezések (%s)', $this->getDateRangeLabel());
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
