<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GlobalSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class GlobalSetting extends Model
{
    /** @use HasFactory<GlobalSettingFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'google_service_account',
    ];

    /**
     * Get the singleton instance of global settings.
     */
    public static function instance(): self
    {
        return self::query()->firstOrCreate([]);
    }

    /**
     * Get the service account credentials.
     *
     * @return array<string, mixed>|null
     */
    public function getServiceAccount(): ?array
    {
        if (! $this->google_service_account) {
            return null;
        }

        $path = storage_path('app/' . $this->google_service_account);

        if (! file_exists($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($contents, true);

        return $decoded;
    }
}
