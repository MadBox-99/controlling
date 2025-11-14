<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;

use function Pest\Laravel\actingAs;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    // Create roles and permissions for testing
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);
});

it('creates all required permissions', function (): void {
    $expectedPermissions = [
        'view teams',
        'create teams',
        'update teams',
        'delete teams',
        'manage team users',
        'view users',
        'create users',
        'update users',
        'delete users',
        'view kpis',
        'create kpis',
        'update kpis',
        'delete kpis',
        'view analytics',
        'manage analytics',
        'view search data',
        'manage search data',
    ];

    foreach ($expectedPermissions as $permission) {
        expect(Permission::where('name', $permission)->exists())->toBeTrue();
    }
});

it('assigns all permissions to admin role', function (): void {
    $adminRole = Role::findByName('admin', 'web');

    expect($adminRole->permissions->count())->toBeGreaterThan(0);
    expect($adminRole->hasPermissionTo('create teams'))->toBeTrue();
    expect($adminRole->hasPermissionTo('delete teams'))->toBeTrue();
    expect($adminRole->hasPermissionTo('manage team users'))->toBeTrue();
});

it('assigns limited permissions to user role', function (): void {
    $userRole = Role::findByName('user', 'web');

    expect($userRole->hasPermissionTo('view teams'))->toBeTrue();
    expect($userRole->hasPermissionTo('view kpis'))->toBeTrue();
    expect($userRole->hasPermissionTo('create teams'))->toBeFalse();
    expect($userRole->hasPermissionTo('delete teams'))->toBeFalse();
});

it('allows admin users to have all permissions', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    actingAs($admin);

    expect($admin->can('create teams'))->toBeTrue();
    expect($admin->can('delete teams'))->toBeTrue();
    expect($admin->can('manage team users'))->toBeTrue();
    expect($admin->can('create users'))->toBeTrue();
    expect($admin->can('delete users'))->toBeTrue();
});

it('allows user role to have limited permissions', function (): void {
    $user = User::factory()->create();
    $user->assignRole('user');

    actingAs($user);

    expect($user->can('view teams'))->toBeTrue();
    expect($user->can('view kpis'))->toBeTrue();
    expect($user->can('create teams'))->toBeFalse();
    expect($user->can('delete teams'))->toBeFalse();
});

it('allows direct permission assignment to users', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('create teams');

    actingAs($user);

    expect($user->can('create teams'))->toBeTrue();
    expect($user->can('delete teams'))->toBeFalse();
});

it('allows checking multiple permissions at once', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    actingAs($admin);

    expect($admin->hasAllPermissions(['create teams', 'delete teams', 'manage team users']))->toBeTrue();
    expect($admin->hasAnyPermission(['create teams', 'non-existent permission']))->toBeTrue();
});

it('revokes permissions correctly', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('create teams');

    expect($user->can('create teams'))->toBeTrue();

    $user->revokePermissionTo('create teams');

    expect($user->can('create teams'))->toBeFalse();
});
