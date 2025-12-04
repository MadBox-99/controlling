<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TeamCreateRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

final class TeamController extends Controller
{
    public function create(TeamCreateRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $team = Team::query()->create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
        ]);
        Log::info('Team created', ['team_id' => $team->id]);
        Log::info('Team details', ['name' => $team->name, 'slug' => $team->slug]);
        // Attach user to team if email provided
        if (isset($validated['user_email'])) {
            $user = User::query()->where('email', $validated['user_email'])->first();
            if ($user) {
                $user->teams()->attach($team);
            }
        }
        Log::info('User attached to team', ['user_email' => $validated['user_email'] ?? 'N/A', 'team_id' => $team->id]);

        return response()->json([
            'message' => 'Team created successfully',
            'team_id' => $team->id,
        ], 201);
    }

    public function getUserTeams(Request $request): JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'user_email' => ['required', 'email', 'exists:users,email'],
        ]);

        if ($validated->fails()) {
            return response()->json([
                'errors' => $validated->errors(),
            ], 422);
        }

        $user = User::query()->where('email', $request->input('user_email'))->first();

        $teams = $user->teams()->get(['teams.id']);

        return response()->json([
            'teams' => $teams,
        ], 200);
    }
}
