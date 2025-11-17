<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\KpiCategory;
use App\Enums\KpiDataSource;
use App\Enums\KpiGoalType;
use App\Enums\KpiValueType;
use App\Observers\TenantAwareObserver;
use Database\Factories\KpiFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(TenantAwareObserver::class)]
final class Kpi extends Model
{
    /** @use HasFactory<KpiFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'code',
        'name',
        'description',
        'data_source',
        'source_type',
        'category',
        'formula',
        'format',
        'target_value',
        'target_date',
        'from_date',
        'goal_type',
        'value_type',
        'page_path',
        'metric_type',
        'is_active',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    protected function casts(): array
    {
        return [
            'data_source' => KpiDataSource::class,
            'category' => KpiCategory::class,
            'target_value' => 'decimal:2',
            'target_date' => 'date',
            'from_date' => 'date',
            'goal_type' => KpiGoalType::class,
            'value_type' => KpiValueType::class,
            'is_active' => 'boolean',
        ];
    }
}
