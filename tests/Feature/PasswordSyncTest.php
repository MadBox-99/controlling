<?php

declare(strict_types=1);

use App\Jobs\SyncPasswordToSecondaryApp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

it('dispatches sync password job when password is updated', function () {
    Queue::fake();

    $user = User::factory()->create([
        'password' => 'old-password',
    ]);

    $user->update([
        'password' => 'new-password',
    ]);

    Queue::assertPushed(SyncPasswordToSecondaryApp::class, function ($job) use ($user) {
        return $job->email === $user->email
            && Hash::check('new-password', $job->hashedPassword);
    });
});

it('does not dispatch sync password job when other fields are updated', function () {
    Queue::fake();

    $user = User::factory()->create([
        'name' => 'Old Name',
    ]);

    $user->update([
        'name' => 'New Name',
    ]);

    Queue::assertNotPushed(SyncPasswordToSecondaryApp::class);
});

it('sends password sync request to secondary app', function () {
    Http::fake([
        'https://secondary-app.test/api/sync-password' => Http::response(['success' => true], 200),
    ]);

    config([
        'services.secondary_app.url' => 'https://secondary-app.test',
        'services.secondary_app.api_key' => 'a1b2c3d4-e5f6a7b8-c9d0e1f2-a3b4c5d6',
    ]);

    $user = User::factory()->create();
    $hashedPassword = Hash::make('new-password');

    $job = new SyncPasswordToSecondaryApp(
        email: $user->email,
        hashedPassword: $hashedPassword,
    );

    $job->handle();

    Http::assertSent(function ($request) use ($user, $hashedPassword) {
        return $request->url() === 'https://secondary-app.test/api/sync-password'
            && $request->hasHeader('Authorization', 'Bearer a1b2c3d4-e5f6a7b8-c9d0e1f2-a3b4c5d6')
            && $request['email'] === $user->email
            && $request['password_hash'] === $hashedPassword;
    });
});

it('skips sync when secondary app configuration is missing', function () {
    Http::fake();

    config([
        'services.secondary_app.url' => null,
        'services.secondary_app.api_key' => null,
    ]);

    $user = User::factory()->create();

    $job = new SyncPasswordToSecondaryApp(
        email: $user->email,
        hashedPassword: Hash::make('password'),
    );

    $job->handle();

    Http::assertNothingSent();
});
