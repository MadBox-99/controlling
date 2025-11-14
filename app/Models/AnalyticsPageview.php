<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\TenantAwareObserver;
use Database\Factories\AnalyticsPageviewFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(TenantAwareObserver::class)]
final class AnalyticsPageview extends Model
{
    /** @use HasFactory<AnalyticsPageviewFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'date',
        'page_path',
        'page_title',
        'pageviews',
        'unique_pageviews',
        'avg_time_on_page',
        'entrances',
        'bounce_rate',
        'exit_rate',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'pageviews' => 'integer',
            'unique_pageviews' => 'integer',
            'avg_time_on_page' => 'integer',
            'entrances' => 'integer',
            'bounce_rate' => 'decimal:2',
            'exit_rate' => 'decimal:2',
        ];
    }
}
