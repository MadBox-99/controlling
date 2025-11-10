<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Support\SourcePageModel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

final class SourcePageBreakdown extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => SourcePageModel::query())
            ->columns([
                TextColumn::make('source')
                    ->label('Source')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('medium')
                    ->label('Medium')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('page_path')
                    ->label('Page Path')
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                TextColumn::make('page_title')
                    ->label('Page Title')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->limit(50),
                TextColumn::make('sessions')
                    ->label('Sessions')
                    ->sortable()
                    ->numeric()
                    ->alignEnd(),
                TextColumn::make('users')
                    ->label('Users')
                    ->sortable()
                    ->numeric()
                    ->alignEnd(),
                TextColumn::make('pageviews')
                    ->label('Pageviews')
                    ->sortable()
                    ->numeric()
                    ->alignEnd(),
            ])
            ->defaultSort('sessions', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    protected function getTableHeading(): string
    {
        return 'Source & Page Breakdown (Last 30 Days)';
    }
}
