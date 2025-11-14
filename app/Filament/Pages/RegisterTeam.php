<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Team;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

final class RegisterTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register Team';
    }

    public static function canAccess(): bool
    {
        return Gate::allows('create', Team::class);
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
                ->unique(Team::class, 'slug')
                ->alphaDash()
                ->helperText('This will be used in the URL: /admin/{slug}/...'),
        ]);
    }

    protected function handleRegistration(array $data): Team
    {
        $team = Team::create($data);

        $team->users()->attach(auth()->user());

        return $team;
    }
}
