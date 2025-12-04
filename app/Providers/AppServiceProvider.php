<?php

declare(strict_types=1);

namespace App\Providers;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Implicitly grant "Super-Admin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super-Admin') ? true : null;
        });

        // Table Columns
        TextColumn::configureUsing(fn (TextColumn $column) => $column->translateLabel());
        IconColumn::configureUsing(fn (IconColumn $column) => $column->translateLabel());

        // Table Filters
        SelectFilter::configureUsing(fn (SelectFilter $filter) => $filter->translateLabel());
        TernaryFilter::configureUsing(fn (TernaryFilter $filter) => $filter->translateLabel());

        // Form Components
        TextInput::configureUsing(fn (TextInput $input) => $input->translateLabel());
        Textarea::configureUsing(fn (Textarea $textarea) => $textarea->translateLabel());
        Select::configureUsing(fn (Select $select) => $select->translateLabel());
        DatePicker::configureUsing(fn (DatePicker $picker) => $picker->translateLabel());
        FileUpload::configureUsing(fn (FileUpload $upload) => $upload->translateLabel());
        Toggle::configureUsing(fn (Toggle $toggle) => $toggle->translateLabel());
        Repeater::configureUsing(fn (Repeater $repeater) => $repeater->translateLabel());
        Action::configureUsing(fn (Action $action) => $action->translateLabel());
    }
}
