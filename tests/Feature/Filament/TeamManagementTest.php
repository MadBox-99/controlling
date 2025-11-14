<?php

declare(strict_types=1);

use App\Filament\Pages\EditTeamProfile;
use App\Filament\Pages\RegisterTeam;
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

it('can render register team page', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RegisterTeam::class)
        ->assertSuccessful();
});

it('can create new team', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RegisterTeam::class)
        ->set('data.name', 'New Test Team')
        ->set('data.slug', 'new-test-team')
        ->call('register')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Team::class, [
        'name' => 'New Test Team',
        'slug' => 'new-test-team',
    ]);
});

it('automatically generates slug from name when creating team', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RegisterTeam::class)
        ->set('data.name', 'Amazing New Team')
        ->assertSet('data.slug', 'amazing-new-team');
});

it('automatically attaches creator to new team', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RegisterTeam::class)
        ->set('data.name', 'Creator Test Team')
        ->set('data.slug', 'creator-test-team')
        ->call('register');

    $team = Team::where('slug', 'creator-test-team')->first();
    expect($team->users->contains($this->admin))->toBeTrue();
});

it('validates required fields when creating team', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RegisterTeam::class)
        ->set('data.name', '')
        ->set('data.slug', '')
        ->call('register')
        ->assertHasFormErrors(['name' => 'required', 'slug' => 'required']);
});

it('validates unique slug when creating team', function (): void {
    Team::factory()->create(['slug' => 'existing-slug']);

    Livewire::actingAs($this->admin)
        ->test(RegisterTeam::class)
        ->set('data.name', 'Test Team')
        ->set('data.slug', 'existing-slug')
        ->call('register')
        ->assertHasFormErrors(['slug']);
});

it('validates slug format (alpha-dash)', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RegisterTeam::class)
        ->set('data.name', 'Test Team')
        ->set('data.slug', 'invalid slug!')
        ->call('register')
        ->assertHasFormErrors(['slug']);
});

it('non-admin users cannot access register team page', function (): void {
    Livewire::actingAs($this->user)
        ->test(RegisterTeam::class)
        ->assertStatus(404);
});

it('can render edit team profile page', function (): void {
    Livewire::actingAs($this->admin)
        ->test(EditTeamProfile::class, ['tenant' => $this->team->getRouteKey()])
        ->assertSuccessful();
});

it('can retrieve team data for editing', function (): void {
    Livewire::actingAs($this->admin)
        ->test(EditTeamProfile::class, ['tenant' => $this->team->getRouteKey()])
        ->assertSet('data.name', $this->team->name)
        ->assertSet('data.slug', $this->team->slug);
});

it('can update team profile', function (): void {
    Livewire::actingAs($this->admin)
        ->test(EditTeamProfile::class, ['tenant' => $this->team->getRouteKey()])
        ->set('data.name', 'Updated Team Name')
        ->set('data.slug', 'updated-team-slug')
        ->call('save')
        ->assertHasNoFormErrors();

    expect($this->team->refresh()->name)->toBe('Updated Team Name');
    expect($this->team->slug)->toBe('updated-team-slug');
});

it('validates unique slug when updating team (ignores current record)', function (): void {
    $otherTeam = Team::factory()->create(['slug' => 'other-team-slug']);

    // Should allow keeping the same slug
    Livewire::actingAs($this->admin)
        ->test(EditTeamProfile::class, ['tenant' => $this->team->getRouteKey()])
        ->set('data.name', $this->team->name)
        ->set('data.slug', $this->team->slug)
        ->call('save')
        ->assertHasNoFormErrors();

    // Should not allow using another team's slug
    Livewire::actingAs($this->admin)
        ->test(EditTeamProfile::class, ['tenant' => $this->team->getRouteKey()])
        ->set('data.name', 'Test')
        ->set('data.slug', 'other-team-slug')
        ->call('save')
        ->assertHasFormErrors(['slug']);
});

it('non-admin users cannot access edit team profile page', function (): void {
    Livewire::actingAs($this->user)
        ->test(EditTeamProfile::class, ['tenant' => $this->team->getRouteKey()])
        ->assertStatus(404);
});

it('validates required fields when updating team', function (): void {
    Livewire::actingAs($this->admin)
        ->test(EditTeamProfile::class, ['tenant' => $this->team->getRouteKey()])
        ->set('data.name', '')
        ->set('data.slug', '')
        ->call('save')
        ->assertHasFormErrors(['name' => 'required', 'slug' => 'required']);
});
