<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

use Spatie\Permission\Models\Role;

beforeEach(function () {
    config(['services.subscriber_api_key' => 'test-api-key']);
    Role::findOrCreate('subscriber', 'web');
    Role::findOrCreate('manager', 'web');
});

describe('create', function () {
    it('creates a user successfully', function () {
        $response = postJson('/api/create-user', [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'password_hash' => 'hashed_password',
            'role' => 'subscriber',
        ], ['Authorization' => 'Bearer test-api-key']);

        $response->assertCreated()
            ->assertJsonStructure(['message', 'user_id']);

        assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        expect($user->hasRole('subscriber'))->toBeTrue();
    });

    it('returns unauthorized without api key', function () {
        $response = postJson('/api/create-user', [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'password_hash' => 'hashed_password',
            'role' => 'subscriber',
        ]);

        $response->assertUnauthorized();
    });

    it('validates required fields', function (string $field) {
        $data = [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'password_hash' => 'hashed_password',
            'role' => 'subscriber',
        ];

        unset($data[$field]);

        $response = postJson('/api/create-user', $data, [
            'Authorization' => 'Bearer test-api-key',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors($field);
    })->with(['email', 'name', 'password_hash', 'role']);

    it('validates email uniqueness', function () {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = postJson('/api/create-user', [
            'email' => 'existing@example.com',
            'name' => 'Test User',
            'password_hash' => 'hashed_password',
            'role' => 'subscriber',
        ], ['Authorization' => 'Bearer test-api-key']);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    });

    it('validates role values', function () {
        $response = postJson('/api/create-user', [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'password_hash' => 'hashed_password',
            'role' => 'invalid_role',
        ], ['Authorization' => 'Bearer test-api-key']);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('role');
    });
});

describe('sync', function () {
    it('syncs user data successfully', function () {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);
        $user->assignRole('subscriber');

        $response = postJson('/api/sync-user', [
            'email' => 'user@example.com',
            'new_email' => 'updated@example.com',
            'role' => 'manager',
        ], ['Authorization' => 'Bearer test-api-key']);

        $response->assertOk()
            ->assertJson(['message' => 'User synced successfully']);

        assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'updated@example.com',
        ]);

        $user->refresh();
        expect($user->hasRole('manager'))->toBeTrue();
    });

    it('returns validation error for non-existent user', function () {
        $response = postJson('/api/sync-user', [
            'email' => 'nonexistent@example.com',
            'role' => 'manager',
        ], ['Authorization' => 'Bearer test-api-key']);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    });

    it('validates new email uniqueness', function () {
        User::factory()->create(['email' => 'existing@example.com']);
        User::factory()->create(['email' => 'user@example.com']);

        $response = postJson('/api/sync-user', [
            'email' => 'user@example.com',
            'new_email' => 'existing@example.com',
        ], ['Authorization' => 'Bearer test-api-key']);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('new_email');
    });

    it('updates password hash', function () {
        Http::fake();

        $user = User::factory()->create(['email' => 'user@example.com']);
        $oldPassword = $user->password;

        $response = postJson('/api/sync-user', [
            'email' => 'user@example.com',
            'password_hash' => 'new_hashed_password',
        ], ['Authorization' => 'Bearer test-api-key']);

        $response->assertOk();

        $user->refresh();
        expect($user->password)->not->toBe($oldPassword);
    });
});
