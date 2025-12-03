<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\postJson;

beforeEach(function (): void {
    config(['services.subscriber_api_key' => 'test-api-key']);
});

it('successfully syncs password from secondary app', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'old-password',
    ]);

    $newPasswordHash = Hash::make('new-password');

    $response = postJson('/api/sync-password', [
        'email' => 'test@example.com',
        'password_hash' => $newPasswordHash,
    ], [
        'Authorization' => 'Bearer test-api-key',
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'message' => 'Password synced successfully.',
    ]);

    $user->refresh();
    expect(Hash::check('new-password', $user->password))->toBeTrue();
});

it('does not trigger observer when syncing password from API', function (): void {
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
        'Authorization' => 'Bearer test-api-key',
    ])->assertSuccessful();

    Queue::assertNothingPushed();
});

it('requires valid api key', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = postJson('/api/sync-password', [
        'email' => 'test@example.com',
        'password_hash' => Hash::make('new-password'),
    ], [
        'Authorization' => 'Bearer wrong-api-key',
    ]);

    $response->assertUnauthorized();
});

it('requires email field', function (): void {
    $response = postJson('/api/sync-password', [
        'password_hash' => Hash::make('new-password'),
    ], [
        'Authorization' => 'Bearer test-api-key',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email']);
});

it('requires password_hash field', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = postJson('/api/sync-password', [
        'email' => 'test@example.com',
    ], [
        'Authorization' => 'Bearer test-api-key',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['password_hash']);
});

it('requires existing user email', function (): void {
    $response = postJson('/api/sync-password', [
        'email' => 'nonexistent@example.com',
        'password_hash' => Hash::make('new-password'),
    ], [
        'Authorization' => 'Bearer test-api-key',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email']);
});

it('requires password_hash to be at least 60 characters', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = postJson('/api/sync-password', [
        'email' => 'test@example.com',
        'password_hash' => 'short',
    ], [
        'Authorization' => 'Bearer test-api-key',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['password_hash']);
});
