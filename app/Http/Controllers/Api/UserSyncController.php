<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserSyncCreateRequest;
use App\Http\Requests\Api\UserSyncRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

final class UserSyncController extends Controller
{
    public function create(UserSyncCreateRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'email' => $validated['email'],
            'name' => $validated['name'],
            'password' => 'temporary',
        ]);
        foreach ($validated['team_ids'] ?? [] as $teamId) {
            Log::info('Assigning team to created user', ['email' => $user->email, 'team_id' => $teamId]);
        }

        $user->teams()->attach($validated['team_ids']);

        // Bypass the hashed cast - password is already hashed
        User::where('id', $user->id)->update([
            'password' => $validated['password_hash'],
            'email_verified_at' => now(),
        ]);

        $user->assignRole($validated['role']);

        return response()->json([
            'message' => 'User created successfully',
            'user_id' => $user->id,
        ], 201);
    }

    public function sync(UserSyncRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->firstOrFail();

        $updateData = [];

        if (isset($validated['new_email'])) {
            $updateData['email'] = $validated['new_email'];
        }
        if (isset($validated['password_hash'])) {
            $updateData['password'] = $validated['password_hash'];
        }

        if ($updateData !== []) {
            // Bypass the hashed cast - password is already hashed
            User::where('id', $user->id)->update($updateData);
        }

        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return response()->json(['message' => 'User synced successfully']);
    }
}
