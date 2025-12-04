<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;

use function Pest\Laravel\actingAs;

use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    // Create roles for testing
    Role::findOrCreate('Super-Admin', 'web');
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('subscriber', 'web');
});

it('allows admins to create teams', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Super-Admin');

    actingAs($admin);

    expect($admin->can('create', Team::class))->toBeTrue();
});

it('prevents non-admins from creating teams', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    expect($user->can('create', Team::class))->toBeFalse();
});

it('allows admins to update teams', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Super-Admin');
    $team = Team::factory()->create();

    actingAs($admin);

    expect($admin->can('update', $team))->toBeTrue();
});

it('prevents non-admins from updating teams', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    actingAs($user);

    expect($user->can('update', $team))->toBeFalse();
});

it('allows admins to delete teams', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Super-Admin');
    $team = Team::factory()->create();

    actingAs($admin);

    expect($admin->can('delete', $team))->toBeTrue();
});

it('prevents non-admins from deleting teams', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    actingAs($user);

    expect($user->can('delete', $team))->toBeFalse();
});

it('allows admins to manage team users', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Super-Admin');
    $team = Team::factory()->create();

    actingAs($admin);

    expect($admin->can('manageUsers', $team))->toBeTrue();
});

it('prevents non-admins from managing team users', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    actingAs($user);

    expect($user->can('manageUsers', $team))->toBeFalse();
});

it('allows users to view teams they belong to', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $user->teams()->attach($team);

    actingAs($user);

    expect($user->can('view', $team))->toBeTrue();
});

it('prevents users from viewing teams they do not belong to', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    actingAs($user);

    expect($user->can('view', $team))->toBeFalse();
});

it('allows all authenticated users to view any teams', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    expect($user->can('viewAny', Team::class))->toBeTrue();
});

it('allows users with admin role to create teams', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    actingAs($admin);

    expect($admin->can('create', Team::class))->toBeTrue();
});

it('allows users with admin role to update teams', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $team = Team::factory()->create();

    actingAs($admin);

    expect($admin->can('update', $team))->toBeTrue();
});

it('allows users with admin role to delete teams', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $team = Team::factory()->create();

    actingAs($admin);

    expect($admin->can('delete', $team))->toBeTrue();
});

it('allows users with admin role to manage team users', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $team = Team::factory()->create();

    actingAs($admin);

    expect($admin->can('manageUsers', $team))->toBeTrue();
});
