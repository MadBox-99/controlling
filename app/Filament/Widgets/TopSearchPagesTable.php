<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Support\TopSearchPageModel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

final class TopSearchPagesTable extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected $listeners = ['dateRangeUpdated' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => TopSearchPageModel::query())
            ->columns([
                TextColumn::make('page_url')
                    ->label('Oldal URL')
                    ->searchable()
                    ->wrap()
                    ->weight('bold')
                    ->limit(50),

                TextColumn::make('impressions')
                    ->label('Megjelenítések')
                    ->sortable()
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('clicks')
                    ->label('Kattintások')
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
                    ->label('Átlagos pozíció')
                    ->sortable()
                    ->numeric(decimalPlaces: 1)
                    ->alignEnd(),
            ])
            ->defaultSort('clicks', 'desc')
            ->paginated([10, 25, 50]);
    }

    protected function getTableHeading(): ?string
    {
        return 'Top oldalak (Elmúlt 30 nap)';
    }
}
