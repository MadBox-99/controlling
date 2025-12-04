<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    (new RoleSeeder())->run();
});

it('can be created using factory', function (): void {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->id)->toBeInt();
});

it('has correct fillable attributes', function (): void {
    $user = new User();

    expect($user->getFillable())->toBe([
        'name',
        'email',
        'password',
        'is_active',
        'email_verified_at',
    ]);
});

it('has correct hidden attributes', function (): void {
    $user = new User();

    expect($user->getHidden())->toBe([
        'password',
        'remember_token',
    ]);
});

it('casts is_active to boolean', function (): void {
    $user = User::factory()->create(['is_active' => 1]);

    expect($user->is_active)->toBeBool()
        ->and($user->is_active)->toBeTrue();
});

it('casts email_verified_at to datetime', function (): void {
    $user = User::factory()->create(['email_verified_at' => now()]);

    expect($user->email_verified_at)->toBeInstanceOf(DateTimeInterface::class);
});

it('belongs to many teams', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $user->teams()->attach($team);

    expect($user->teams)->toHaveCount(1)
        ->and($user->teams->first()->id)->toBe($team->id);
});

it('can check if user is super admin', function (): void {
    $user = User::factory()->create();
    $user->assignRole(UserRole::SuperAdmin);

    expect($user->isSuperAdmin())->toBeTrue();
});

it('returns false for is super admin when user has no role', function (): void {
    $user = User::factory()->create();

    expect($user->isSuperAdmin())->toBeFalse();
});

it('can check if user is admin', function (): void {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Admin);

    expect($user->isAdmin())->toBeTrue();
});

it('returns true for is admin when user is super admin', function (): void {
    $user = User::factory()->create();
    $user->assignRole(UserRole::SuperAdmin);

    expect($user->isAdmin())->toBeTrue();
});

it('returns false for is admin when user has no admin role', function (): void {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Subscriber);

    expect($user->isAdmin())->toBeFalse();
});

it('can access tenant when user belongs to team', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $user->teams()->attach($team);

    expect($user->canAccessTenant($team))->toBeTrue();
});

it('cannot access tenant when user does not belong to team', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    expect($user->canAccessTenant($team))->toBeFalse();
});
