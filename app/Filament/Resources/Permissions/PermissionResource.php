<?php

declare(strict_types=1);

namespace App\Filament\Resources\Permissions;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Permissions\Pages\ManagePermissions;
use App\Filament\Resources\Permissions\Schemas\PermissionForm;
use App\Filament\Resources\Permissions\Tables\PermissionsTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use UnitEnum;

final class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Settings;

    protected static ?int $navigationSort = 2;

    protected static bool $isScopedToTenant = false;

    public static function getModelLabel(): string
    {
        return __('Permission');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Permissions');
    }

    public static function form(Schema $schema): Schema
    {
        return PermissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermissionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePermissions::route('/'),
        ];
    }
}
