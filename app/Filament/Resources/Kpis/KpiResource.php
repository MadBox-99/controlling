<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kpis;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Kpis\Pages\CreateKpi;
use App\Filament\Resources\Kpis\Pages\EditKpi;
use App\Filament\Resources\Kpis\Pages\ListKpis;
use App\Filament\Resources\Kpis\Pages\ViewKpi;
use App\Filament\Resources\Kpis\Schemas\KpiForm;
use App\Filament\Resources\Kpis\Tables\KpisTable;
use App\Models\Kpi;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class KpiResource extends Resource
{
    protected static ?string $model = Kpi::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Kpis;

    protected static bool $isScopedToTenant = true;

    public static function form(Schema $schema): Schema
    {
        return KpiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KpisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKpis::route('/'),
            'create' => CreateKpi::route('/create'),
            'view' => ViewKpi::route('/{record}'),
            'edit' => EditKpi::route('/{record}/edit'),
        ];
    }
}
