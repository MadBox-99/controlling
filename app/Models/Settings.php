<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SettingsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Settings extends Model
{
    /** @use HasFactory<SettingsFactory> */
    use HasFactory;

    protected $fillable = [
        'google_service_account',
        'property_id',
        'google_tag_id',
        'site_url',
        'last_sync_at',
    ];

    protected function casts(): array
    {
        return [
            'last_sync_at' => 'datetime',
        ];
    }
}
