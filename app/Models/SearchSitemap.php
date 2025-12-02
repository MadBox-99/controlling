<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SearchSitemapFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class SearchSitemap extends Model
{
    /** @use HasFactory<SearchSitemapFactory> */
    use HasFactory;

    protected $fillable = [
        'sitemap_url',
        'last_submitted',
        'is_pending',
        'warnings',
        'errors',
    ];

    protected function casts(): array
    {
        return [
            'last_submitted' => 'datetime',
            'is_pending' => 'boolean',
            'warnings' => 'integer',
            'errors' => 'integer',
        ];
    }
}
