<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\postJson;

beforeEach(function () {
    config([
        'services.secondary_app.api_key' => 'a1b2c3d4-e5f6a7b8-c9d0e1f2-a3b4c5d6',
    ]);
});

it('successfully syncs password from secondary app', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'old-password',
    ]);

    $newPasswordHash = Hash::make('new-password');

    $response = postJson('/api/sync-password', [
        'email' => 'test@example.com',
        'password_hash' => $newPasswordHash,
    ], [
        'Authorization' => 'Bearer a1b2c3d4-e5f6a7b8-c9d0e1f2-a3b4c5d6',
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'message' => 'Password synced successfully.',
    ]);

    $user->refresh();
    expect(Hash::check('new-password', $user->password))->toBeTrue();
});

it('does not trigger observer when syncing password from API', function () {
    Queue::fake();

    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'old-password',
    ]);

    $newPasswordHash = Hash::make('new-password');

    postJson('/api/sync-password', [
        'email' => 'test@example.com',
        'password_hash' => $newPasswordHash,
    ], [
        'Authorization' => 'Bearer a1b2c3d4-e5f6a7b8-c9d0e1f2-a3b4c5d6',
    ])->assertSuccessful();

    Queue::assertNothingPushed();
});

it('requires valid api key', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = postJson('/api/sync-password', [
        'email' => 'test@example.com',
        'password_hash' => Hash::make('new-password'),
    ], [
        'Authorization' => 'Bearer ffffffff-ffffffff-ffffffff-ffffffff',
    ]);

    $response->assertForbidden();
});

it('rejects invalid api key format', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = postJson('/api/sync-password', [
        'email' => 'test@example.com',
        'password_hash' => Hash::make('new-password'),
    ], [
        'Authorization' => 'Bearer invalid-format-key',
    ]);

    $response->assertForbidden();
});

it('requires email field', function () {
    $response = postJson('/api/sync-password', [
        'password_hash' => Hash::make('new-password'),
    ], [
        'Authorization' => 'Bearer a1b2c3d4-e5f6a7b8-c9d0e1f2-a3b4c5d6',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email']);
});

it('requires password_hash field', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = postJson('/api/sync-password', [
        'email' => 'test@example.com',
    ], [
        'Authorization' => 'Bearer a1b2c3d4-e5f6a7b8-c9d0e1f2-a3b4c5d6',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['password_hash']);
});

it('requires existing user email', function () {
    $response = postJson('/api/sync-password', [
        'email' => 'nonexistent@example.com',
        'password_hash' => Hash::make('new-password'),
    ], [
        'Authorization' => 'Bearer a1b2c3d4-e5f6a7b8-c9d0e1f2-a3b4c5d6',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email']);
});

it('requires password_hash to be at least 60 characters', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = postJson('/api/sync-password', [
        'email' => 'test@example.com',
        'password_hash' => 'short',
    ], [
        'Authorization' => 'Bearer a1b2c3d4-e5f6a7b8-c9d0e1f2-a3b4c5d6',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['password_hash']);
});
