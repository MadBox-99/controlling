<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Support\SessionSourceModel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

final class SessionSourcesTable extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => SessionSourceModel::query())
            ->columns([
                TextColumn::make('source')
                    ->label('Munkamenet forrása/médium')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (SessionSourceModel $record): string => $record->medium),

                TextColumn::make('sessions')
                    ->label('Munkamenetek')
                    ->sortable()
                    ->numeric()
                    ->alignEnd(),
            ])
            ->defaultSort('sessions', 'desc')
            ->paginated([10, 25, 50]);
    }

    protected function getTableHeading(): string
    {
        return 'Munkamenetek / Munkamenet forrása/médium';
    }
}
