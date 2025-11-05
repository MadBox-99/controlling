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
                    ->label('Eszköz')
                    ->weight('bold'),

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
            ])
            ->defaultSort('clicks', 'desc')
            ->paginated(false);
    }

    protected function getTableHeading(): ?string
    {
        return 'Eszközök (Elmúlt 30 nap)';
    }
}
