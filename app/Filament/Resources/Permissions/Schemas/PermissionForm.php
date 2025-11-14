<?php

declare(strict_types=1);

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Permission Details')
                ->schema([
                    TextInput::make('name')
                        ->label('Permission Name')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->placeholder('e.g., view users, create posts')
                        ->helperText('Use a descriptive name like: view users, create teams, delete kpis'),

                    TextInput::make('guard_name')
                        ->label('Guard Name')
                        ->default('web')
                        ->required()
                        ->maxLength(255),
                ]),

            Section::make('Roles')
                ->schema([
                    Select::make('roles')
                        ->label('Assign to Roles')
                        ->multiple()
                        ->relationship('roles', 'name')
                        ->preload()
                        ->searchable()
                        ->placeholder('Select roles that should have this permission')
                        ->helperText('Choose which roles should automatically have this permission'),
                ]),
        ]);
    }
}
