<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Support\UserSourceModel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

final class UserSourcesTable extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => UserSourceModel::query())
            ->columns([
                TextColumn::make('source')
                    ->label('Első felhasználóhoz tartozó forrás')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (UserSourceModel $record): string => $record->medium),

                TextColumn::make('users')
                    ->label('Aktív felhasználók')
                    ->sortable()
                    ->numeric()
                    ->alignEnd(),
            ])
            ->defaultSort('users', 'desc')
            ->paginated([10, 25, 50]);
    }

    protected function getTableHeading(): string
    {
        return 'Aktív felhasználók / Első felhasználóhoz tartozó forrás';
    }
}
