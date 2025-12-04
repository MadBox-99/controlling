<?php

declare(strict_types=1);

use App\Filament\Resources\Kpis\Pages\EditKpi;
use App\Filament\Resources\Kpis\Pages\ListKpis;
use App\Models\Kpi;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);
/**
 * @return array{team: Team, admin: User, user: User}
 */
function setupKpiTestFixtures(): array
{
    seed(RoleSeeder::class);
    seed(PermissionSeeder::class);

    $team = Team::factory()->create(['name' => 'Test Team']);
    $admin = User::factory()->create();
    $admin->teams()->attach($team);
    $admin->assignRole('Super-Admin');

    $user = User::factory()->create();
    $user->teams()->attach($team);
    $user->assignRole('subscriber');

    actingAs($admin);
    Filament::setTenant($team);

    return ['team' => $team, 'admin' => $admin, 'user' => $user];
}

it('can render kpi list page', function (): void {
    ['team' => $team, 'admin' => $admin] = setupKpiTestFixtures();

    Livewire::actingAs($admin)
        ->test(ListKpis::class, ['tenant' => $team])
        ->assertSuccessful();
});

it('can list kpis in table', function (): void {
    ['team' => $team, 'admin' => $admin] = setupKpiTestFixtures();

    $kpis = Kpi::factory()->count(10)->create(['team_id' => $team->id]);

    Livewire::actingAs($admin)
        ->test(ListKpis::class, ['tenant' => $team])
        ->assertCanSeeTableRecords($kpis);
});

it('can render edit kpi page', function (): void {
    ['team' => $team, 'admin' => $admin] = setupKpiTestFixtures();

    $kpi = Kpi::factory()->create(['team_id' => $team->id]);

    Livewire::actingAs($admin)
        ->test(EditKpi::class, ['record' => $kpi->getRouteKey(), 'tenant' => $team])
        ->assertSuccessful();
});

it('can retrieve kpi data for editing', function (): void {
    ['team' => $team, 'admin' => $admin] = setupKpiTestFixtures();

    $kpi = Kpi::factory()->create(['team_id' => $team->id]);

    Livewire::actingAs($admin)
        ->test(EditKpi::class, ['record' => $kpi->getRouteKey(), 'tenant' => $team])
        ->assertSet('data.code', $kpi->code)
        ->assertSet('data.name', $kpi->name);
});

it('can update kpi', function (): void {
    ['team' => $team, 'admin' => $admin] = setupKpiTestFixtures();

    $kpi = Kpi::factory()->create([
        'team_id' => $team->id,
        'data_source' => 'manual',
        'target_value' => 1000,
        'goal_type' => 'increase',
        'value_type' => 'fixed',
        'from_date' => now(),
        'target_date' => now()->addMonth(),
        'is_active' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(EditKpi::class, ['record' => $kpi->getRouteKey(), 'tenant' => $team])
        ->set('data.is_active', false)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($kpi->refresh()->is_active)->toBe(false);
});

it('can delete kpi', function (): void {
    ['team' => $team, 'admin' => $admin] = setupKpiTestFixtures();

    $kpi = Kpi::factory()->create(['team_id' => $team->id]);

    Livewire::actingAs($admin)
        ->test(EditKpi::class, ['record' => $kpi->getRouteKey(), 'tenant' => $team])
        ->callAction('delete');

    expect(Kpi::find($kpi->id))->toBeNull();
});

it('can search kpis in table', function (): void {
    ['team' => $team, 'admin' => $admin] = setupKpiTestFixtures();

    Kpi::factory()->create(['team_id' => $team->id, 'name' => 'Searchable KPI']);
    Kpi::factory()->create(['team_id' => $team->id, 'name' => 'Another KPI']);

    Livewire::actingAs($admin)
        ->test(ListKpis::class, ['tenant' => $team])
        ->searchTable('Searchable')
        ->assertCanSeeTableRecords(Kpi::query()->where('name', 'Searchable KPI')->get())
        ->assertCanNotSeeTableRecords(Kpi::query()->where('name', 'Another KPI')->get());
});
