<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Support\TopPageModel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

final class TopPagesTable extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => TopPageModel::query())
            ->columns([
                TextColumn::make('page_title')
                    ->label('Oldalcím és képernyőosztály')
                    ->searchable()
                    ->wrap()
                    ->weight('bold')
                    ->description(fn (TopPageModel $record): string => $record->page_path),

                TextColumn::make('views')
                    ->label('Megtekintések')
                    ->sortable()
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('active_users')
                    ->label('Aktív felhasználók')
                    ->sortable()
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('event_count')
                    ->label('Eseményszám')
                    ->sortable()
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('bounce_rate')
                    ->label('Visszafordulási arány')
                    ->sortable()
                    ->numeric(decimalPlaces: 1)
                    ->suffix('%')
                    ->alignEnd(),
            ])
            ->defaultSort('views', 'desc')
            ->paginated([10, 25, 50]);
    }

    protected function getTableHeading(): string
    {
        return 'Legnépszerűbb oldalak/képernyők';
    }
}
