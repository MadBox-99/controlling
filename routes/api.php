<?php

declare(strict_types=1);

use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserSyncController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function () {
    Route::post('/create-user', [UserSyncController::class, 'create']);
    Route::post('/sync-user', [UserSyncController::class, 'sync']);
    Route::post('/create-team', [TeamController::class, 'create']);
    Route::get('/user-teams', [TeamController::class, 'getUserTeams']);
});
