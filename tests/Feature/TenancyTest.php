<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->team = Team::factory()->create();
    $this->user = User::factory()->create();
    $this->user->teams()->attach($this->team);
});

it('can create a team', function (): void {
    $team = Team::factory()->create([
        'name' => 'Test Team',
        'slug' => 'test-team',
    ]);

    expect($team)->toBeInstanceOf(Team::class)
        ->and($team->name)->toBe('Test Team')
        ->and($team->slug)->toBe('test-team');
});

it('can associate users with teams', function (): void {
    expect($this->user->teams)->toHaveCount(1)
        ->and($this->user->teams->first()->id)->toBe($this->team->id);
});

it('user can access tenant they belong to', function (): void {
    actingAs($this->user);

    expect($this->user->canAccessTenant($this->team))->toBeTrue();
});

it('user cannot access tenant they do not belong to', function (): void {
    actingAs($this->user);

    $otherTeam = Team::factory()->create();

    expect($this->user->canAccessTenant($otherTeam))->toBeFalse();
});

it('user can get their tenants', function (): void {
    actingAs($this->user);

    $tenants = $this->user->getTenants(Filament::getPanel('admin'));

    expect($tenants)->toHaveCount(1)
        ->and($tenants->first()->id)->toBe($this->team->id);
});

it('user can belong to multiple teams', function (): void {
    $team2 = Team::factory()->create();
    $team3 = Team::factory()->create();

    $this->user->teams()->attach([$team2->id, $team3->id]);

    expect($this->user->teams)->toHaveCount(3);
});

it('team can have multiple users', function (): void {
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $this->team->users()->attach([$user2->id, $user3->id]);

    expect($this->team->users)->toHaveCount(3);
});

it('deleting team deletes pivot records', function (): void {
    $teamId = $this->team->id;

    $this->team->delete();

    expect(Team::find($teamId))->toBeNull()
        ->and($this->user->fresh()->teams)->toHaveCount(0);
});
