<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Role Details')
                ->schema([
                    TextInput::make('name')
                        ->label('Role Name')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->placeholder('e.g., admin, editor, viewer'),

                    TextInput::make('guard_name')
                        ->label('Guard Name')
                        ->default('web')
                        ->required()
                        ->maxLength(255),
                ]),

            Section::make('Permissions')
                ->schema([
                    Select::make('permissions')
                        ->label('Assign Permissions')
                        ->multiple()
                        ->relationship('permissions', 'name')
                        ->preload()
                        ->searchable()
                        ->placeholder('Select permissions for this role')
                        ->helperText('Choose which permissions this role should have'),
                ]),
        ]);
    }
}
