<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\TenantAwareObserver;
use Database\Factories\AnalyticsSessionFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(TenantAwareObserver::class)]
final class AnalyticsSession extends Model
{
    /** @use HasFactory<AnalyticsSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'date',
        'sessions',
        'users',
        'new_users',
        'bounce_rate',
        'avg_session_duration',
        'pages_per_session',
        'source',
        'medium',
        'campaign',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'sessions' => 'integer',
            'users' => 'integer',
            'new_users' => 'integer',
            'bounce_rate' => 'decimal:2',
            'avg_session_duration' => 'integer',
            'pages_per_session' => 'decimal:2',
        ];
    }
}
