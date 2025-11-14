<?php

declare(strict_types=1);

use App\Filament\Resources\Kpis\Pages\CreateKpi;
use App\Filament\Resources\Kpis\Pages\EditKpi;
use App\Filament\Resources\Kpis\Pages\ListKpis;
use App\Models\Kpi;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);

    $this->team = Team::factory()->create(['name' => 'Test Team']);
    $this->admin = User::factory()->create(['is_super_admin' => true]);
    $this->admin->teams()->attach($this->team);
    $this->admin->assignRole('admin');

    $this->user = User::factory()->create(['is_super_admin' => false]);
    $this->user->teams()->attach($this->team);
    $this->user->assignRole('user');

    actingAs($this->admin);
    Filament::setTenant($this->team);
});

it('can render kpi list page', function (): void {
    Livewire::actingAs($this->admin)
        ->test(ListKpis::class, ['tenant' => $this->team])
        ->assertSuccessful();
});

it('can list kpis in table', function (): void {
    $kpis = Kpi::factory()->count(10)->create(['team_id' => $this->team->id]);

    Livewire::actingAs($this->admin)
        ->test(ListKpis::class, ['tenant' => $this->team])
        ->assertCanSeeTableRecords($kpis);
});

it('can render create kpi page', function (): void {
    Livewire::actingAs($this->admin)
        ->test(CreateKpi::class, ['tenant' => $this->team])
        ->assertSuccessful();
});

it('can create kpi', function (): void {
    Livewire::actingAs($this->admin)
        ->test(CreateKpi::class, ['tenant' => $this->team])
        ->set('data.code', 'TEST_KPI')
        ->set('data.name', 'Test KPI')
        ->set('data.description', 'Test Description')
        ->set('data.data_source', 'manual')
        ->set('data.category', 'traffic')
        ->set('data.is_active', true)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Kpi::class, [
        'code' => 'TEST_KPI',
        'name' => 'Test KPI',
        'team_id' => $this->team->id,
    ]);
});

it('automatically assigns team_id when creating kpi', function (): void {
    Livewire::actingAs($this->admin)
        ->test(CreateKpi::class, ['tenant' => $this->team])
        ->set('data.code', 'AUTO_KPI')
        ->set('data.name', 'Auto Assigned KPI')
        ->set('data.data_source', 'analytics')
        ->set('data.category', 'engagement')
        ->set('data.is_active', true)
        ->call('create')
        ->assertHasNoFormErrors();

    $kpi = Kpi::where('code', 'AUTO_KPI')->first();
    expect($kpi->team_id)->toBe($this->team->id);
});

it('validates required fields when creating kpi', function (): void {
    Livewire::actingAs($this->admin)
        ->test(CreateKpi::class, ['tenant' => $this->team])
        ->set('data.code', '')
        ->set('data.name', '')
        ->call('create')
        ->assertHasFormErrors(['code' => 'required', 'name' => 'required']);
});

it('can render edit kpi page', function (): void {
    $kpi = Kpi::factory()->create(['team_id' => $this->team->id]);

    Livewire::actingAs($this->admin)
        ->test(EditKpi::class, ['record' => $kpi->getRouteKey(), 'tenant' => $this->team])
        ->assertSuccessful();
});

it('can retrieve kpi data for editing', function (): void {
    $kpi = Kpi::factory()->create(['team_id' => $this->team->id]);

    Livewire::actingAs($this->admin)
        ->test(EditKpi::class, ['record' => $kpi->getRouteKey(), 'tenant' => $this->team])
        ->assertSet('data.code', $kpi->code)
        ->assertSet('data.name', $kpi->name);
});

it('can update kpi', function (): void {
    $kpi = Kpi::factory()->create(['team_id' => $this->team->id]);

    Livewire::actingAs($this->admin)
        ->test(EditKpi::class, ['record' => $kpi->getRouteKey(), 'tenant' => $this->team])
        ->set('data.name', 'Updated KPI Name')
        ->call('save')
        ->assertHasNoFormErrors();

    expect($kpi->refresh()->name)->toBe('Updated KPI Name');
});

it('can delete kpi', function (): void {
    $kpi = Kpi::factory()->create(['team_id' => $this->team->id]);

    Livewire::actingAs($this->admin)
        ->test(EditKpi::class, ['record' => $kpi->getRouteKey(), 'tenant' => $this->team])
        ->callAction('delete');

    $this->assertModelMissing($kpi);
});

it('non-admin users cannot create kpis', function (): void {
    Livewire::actingAs($this->user)
        ->test(CreateKpi::class, ['tenant' => $this->team])
        ->assertStatus(403);
});

it('can search kpis in table', function (): void {
    Kpi::factory()->create(['team_id' => $this->team->id, 'name' => 'Searchable KPI']);
    Kpi::factory()->create(['team_id' => $this->team->id, 'name' => 'Another KPI']);

    Livewire::actingAs($this->admin)
        ->test(ListKpis::class, ['tenant' => $this->team])
        ->searchTable('Searchable')
        ->assertCanSeeTableRecords(Kpi::where('name', 'Searchable KPI')->get())
        ->assertCanNotSeeTableRecords(Kpi::where('name', 'Another KPI')->get());
});
