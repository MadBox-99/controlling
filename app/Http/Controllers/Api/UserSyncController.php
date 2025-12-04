<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserSyncCreateRequest;
use App\Http\Requests\Api\UserSyncRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

final class UserSyncController extends Controller
{
    public function create(UserSyncCreateRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Create user with raw password - we hash it here
        $user = User::query()->create([
            'email' => $validated['email'],
            'name' => $validated['name'],
            'password' => Hash::make($validated['password']), // Raw password from main app
            'email_verified_at' => now(),
        ]);
        $user->assignRole(UserRole::Subscriber);
        // Assign teams
        $teamIds = $validated['team_ids'] ?? [];
        if ($teamIds !== []) {
            $user->teams()->sync($teamIds);
            Log::info('Assigned teams to created user', ['email' => $user->email, 'team_ids' => $teamIds]);
        }

        // Assign role
        if (isset($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        Log::info('User created successfully via sync', ['email' => $user->email]);

        return response()->json([
            'message' => 'User created successfully',
            'user_id' => $user->id,
        ], 201);
    }

    public function sync(UserSyncRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::query()->where('email', $validated['email'])->firstOrFail();

        $updateData = [];

        if (isset($validated['new_email'])) {
            $updateData['email'] = $validated['new_email'];
        }

        // Raw password from main app - hash it here
        if (isset($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        if ($updateData !== []) {
            // Use saveQuietly to avoid triggering observers (prevents sync loops)
            $user->fill($updateData);
            $user->saveQuietly();
            Log::info('User synced successfully', ['email' => $validated['email'], 'fields' => array_keys($updateData)]);
        }

        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return response()->json(['message' => 'User synced successfully']);
    }

    public function toggleActive(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'is_active' => ['required', 'boolean'],
        ]);

        User::where('email', $validated['email'])
            ->update(['is_active' => $validated['is_active']]);

        return response()->json(['message' => 'User updated']);
    }
}
