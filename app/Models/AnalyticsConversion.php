<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\TenantAwareObserver;
use Database\Factories\AnalyticsConversionFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(TenantAwareObserver::class)]
final class AnalyticsConversion extends Model
{
    /** @use HasFactory<AnalyticsConversionFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'date',
        'goal_name',
        'goal_completions',
        'goal_value',
        'conversion_rate',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'goal_completions' => 'integer',
            'goal_value' => 'decimal:2',
            'conversion_rate' => 'decimal:2',
        ];
    }
}
