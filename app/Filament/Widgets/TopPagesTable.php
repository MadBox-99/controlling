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
                    ->label('Page Title and Screen Class')
                    ->searchable()
                    ->wrap()
                    ->weight('bold')
                    ->description(fn (TopPageModel $record): string => $record->page_path),

                TextColumn::make('views')
                    ->label('Views')
                    ->sortable()
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('active_users')
                    ->label('Active Users')
                    ->sortable()
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('event_count')
                    ->label('Event Count')
                    ->sortable()
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('bounce_rate')
                    ->label('Bounce Rate')
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
