<?php

declare(strict_types=1);

namespace App\Services;

use Google\Client;
use Illuminate\Support\Facades\Storage;

final class GoogleClientFactory
{
    /**
     * @param  array<string>|string  $scopes
     */
    public static function make(array|string $scopes, string $serviceAccountPath): Client
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->setScopes($scopes);
        $client->setAuthConfig(Storage::json($serviceAccountPath));

        return $client;
    }
}
