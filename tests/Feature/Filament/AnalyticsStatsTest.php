<?php

declare(strict_types=1);

use App\Filament\Pages\AnalyticsStats;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);
beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);

    $this->team = Team::factory()->create(['name' => 'Test Team']);
    $this->admin = User::factory()->create();
    $this->admin->teams()->attach($this->team);
    $this->admin->assignRole('Super-Admin');

    actingAs($this->admin);
    Filament::setTenant($this->team);
});

it('can render analytics stats page', function (): void {
    Livewire::actingAs($this->admin)
        ->test(AnalyticsStats::class, ['tenant' => $this->team])
        ->assertSuccessful();
});

it('has set kpi goal action', function (): void {
    Livewire::actingAs($this->admin)
        ->test(AnalyticsStats::class, ['tenant' => $this->team])
        ->assertActionExists('setKpiGoal');
});

it('validates required fields when setting kpi goal', function (): void {
    Livewire::actingAs($this->admin)
        ->test(AnalyticsStats::class, ['tenant' => $this->team])
        ->mountAction('setKpiGoal')
        ->setActionData([])
        ->callMountedAction()
        ->assertHasActionErrors([
            'page_path' => 'required',
            'metric_type' => 'required',
            'target_date' => 'required',
            'goal_type' => 'required',
            'value_type' => 'required',
            'target_value' => 'required',
        ]);
});
