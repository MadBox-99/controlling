<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\TenantAwareObserver;
use Database\Factories\SearchPageFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(TenantAwareObserver::class)]
final class SearchPage extends Model
{
    /** @use HasFactory<SearchPageFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'date',
        'page_url',
        'country',
        'device',
        'impressions',
        'clicks',
        'ctr',
        'position',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'impressions' => 'integer',
            'clicks' => 'integer',
            'ctr' => 'decimal:2',
            'position' => 'decimal:2',
        ];
    }
}
