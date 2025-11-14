<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Team;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

final class EditTeamProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Team Profile';
    }

    public static function canAccess(): bool
    {
        return Gate::allows('update', Team::class);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Team Name')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
            TextInput::make('slug')
                ->label('Team Slug')
                ->required()
                ->maxLength(255)
                ->unique(Team::class, 'slug', ignoreRecord: true)
                ->alphaDash()
                ->helperText('This will be used in the URL: /admin/{slug}/...'),
        ]);
    }
}
