<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ValidateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = config('services.subscriber_api_key');

        if ($request->bearerToken() !== $apiKey) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
