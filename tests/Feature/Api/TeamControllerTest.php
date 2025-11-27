<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function () {
    config(['services.subscriber_api_key' => 'test-api-key']);
});

describe('create', function () {
    it('creates a team successfully', function () {
        $response = postJson('/api/create-team', [
            'name' => 'Test Team',
            'slug' => 'test-team',
        ], ['Authorization' => 'Bearer test-api-key']);

        $response->assertCreated()
            ->assertJsonStructure(['message', 'team_id']);

        assertDatabaseHas('teams', [
            'name' => 'Test Team',
            'slug' => 'test-team',
        ]);
    });

    it('creates a team and attaches user when email provided', function () {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $response = postJson('/api/create-team', [
            'name' => 'Test Team',
            'slug' => 'test-team',
            'user_email' => 'user@example.com',
        ], ['Authorization' => 'Bearer test-api-key']);

        $response->assertCreated();

        $team = Team::where('slug', 'test-team')->first();
        expect($user->teams->contains($team))->toBeTrue();
    });

    it('returns unauthorized without api key', function () {
        $response = postJson('/api/create-team', [
            'name' => 'Test Team',
            'slug' => 'test-team',
        ]);

        $response->assertUnauthorized();
    });

    it('validates required fields', function (string $field) {
        $data = [
            'name' => 'Test Team',
            'slug' => 'test-team',
        ];

        unset($data[$field]);

        $response = postJson('/api/create-team', $data, [
            'Authorization' => 'Bearer test-api-key',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors($field);
    })->with(['name', 'slug']);

    it('validates slug uniqueness', function () {
        Team::factory()->create(['slug' => 'existing-slug']);

        $response = postJson('/api/create-team', [
            'name' => 'Test Team',
            'slug' => 'existing-slug',
        ], ['Authorization' => 'Bearer test-api-key']);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('slug');
    });

    it('validates user_email exists', function () {
        $response = postJson('/api/create-team', [
            'name' => 'Test Team',
            'slug' => 'test-team',
            'user_email' => 'nonexistent@example.com',
        ], ['Authorization' => 'Bearer test-api-key']);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('user_email');
    });
});

describe('getUserTeams', function () {
    it('returns teams for a user', function () {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $teams = Team::factory()->count(3)->create();
        $user->teams()->attach($teams);

        $response = getJson('/api/user-teams?user_email=user@example.com', [
            'Authorization' => 'Bearer test-api-key',
        ]);

        $response->assertOk()
            ->assertJsonCount(3, 'teams')
            ->assertJsonStructure([
                'teams' => [
                    '*' => ['id'],
                ],
            ]);
    });

    it('returns empty array when user has no teams', function () {
        User::factory()->create(['email' => 'user@example.com']);

        $response = getJson('/api/user-teams?user_email=user@example.com', [
            'Authorization' => 'Bearer test-api-key',
        ]);

        $response->assertOk()
            ->assertJsonCount(0, 'teams');
    });

    it('returns unauthorized without api key', function () {
        User::factory()->create(['email' => 'user@example.com']);

        $response = getJson('/api/user-teams?user_email=user@example.com');

        $response->assertUnauthorized();
    });

    it('validates user_email is required', function () {
        $response = getJson('/api/user-teams', [
            'Authorization' => 'Bearer test-api-key',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('user_email');
    });

    it('validates user_email format', function () {
        $response = getJson('/api/user-teams?user_email=invalid-email', [
            'Authorization' => 'Bearer test-api-key',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('user_email');
    });

    it('validates user_email exists', function () {
        $response = getJson('/api/user-teams?user_email=nonexistent@example.com', [
            'Authorization' => 'Bearer test-api-key',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('user_email');
    });
});
