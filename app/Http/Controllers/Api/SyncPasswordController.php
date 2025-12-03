<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SyncPasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class SyncPasswordController extends Controller
{
    public function __invoke(SyncPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Update password directly without triggering observer
        User::query()
            ->where('email', $validated['email'])
            ->update(['password' => $validated['password_hash']]);

        return response()->json([
            'success' => true,
            'message' => 'Password synced successfully.',
        ]);
    }
}
