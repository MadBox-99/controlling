<?php

declare(strict_types=1);

use App\Filament\Pages\RegisterTeam;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);
});

it('subscriber without team cannot create new team', function (): void {
    // Create a regular user without team
    $user = User::factory()->create([
        'is_active' => true,
    ]);
    $user->assignRole('subscriber');

    // User has no teams
    expect($user->teams)->toHaveCount(0);

    // Try to access team registration page
    actingAs($user);

    Livewire::actingAs($user)
        ->test(RegisterTeam::class)
        ->assertStatus(404); // Should be forbidden/not found because user is not admin
});

it('subscriber without team cannot create team via gate', function (): void {
    $user = User::factory()->create([
        'is_active' => true,
    ]);
    $user->assignRole('subscriber');

    actingAs($user);

    expect($user->can('create', Team::class))->toBeFalse();
});

it('user without team but is admin can create team', function (): void {
    $user = User::factory()->create([
        'is_active' => true,
    ]);
    $user->assignRole('Super-Admin');

    // User has no teams yet
    expect($user->teams)->toHaveCount(0);

    actingAs($user);

    // Admin can create team even without having a team
    expect($user->can('create', Team::class))->toBeTrue();
});

it('subscriber without team cannot access filament admin panel', function (): void {
    $user = User::factory()->create([
        'is_active' => true,
    ]);
    $user->assignRole('subscriber');

    actingAs($user);

    // User has no teams, should not be able to access admin panel
    $response = $this->get('/admin');

    // Should redirect, show error, or fail (500) when no tenant is available
    // 302: redirect to login/team selection
    // 403: forbidden
    // 404: not found
    // 500: server error (no tenant available)
    expect($response->getStatusCode())->toBeIn([302, 404, 403, 500]);
});

it('admin user without team can access team registration', function (): void {
    $user = User::factory()->create([
        'is_active' => true,
    ]);
    $user->assignRole('Super-Admin');

    actingAs($user);

    // Admin can access team registration
    Livewire::actingAs($user)
        ->test(RegisterTeam::class)
        ->assertSuccessful();
});

it('seeded admin user has a default team', function (): void {
    // Run the seeder
    $this->seed();

    $admin = User::query()->where('email', 'admin@admin.com')->first();

    expect($admin)->not->toBeNull();
    expect($admin->teams)->toHaveCount(1);
    expect($admin->teams->first()->name)->toBe('Default Team');
    expect($admin->teams->first()->slug)->toBe('default-team');
});
