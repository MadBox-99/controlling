<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function kpis(): HasMany
    {
        return $this->hasMany(Kpi::class);
    }

    public function searchPages(): HasMany
    {
        return $this->hasMany(SearchPage::class);
    }

    public function searchQueries(): HasMany
    {
        return $this->hasMany(SearchQuery::class);
    }

    public function analyticsPageviews(): HasMany
    {
        return $this->hasMany(AnalyticsPageview::class);
    }

    public function analyticsSessions(): HasMany
    {
        return $this->hasMany(AnalyticsSession::class);
    }

    public function analyticsEvents(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class);
    }

    public function analyticsConversions(): HasMany
    {
        return $this->hasMany(AnalyticsConversion::class);
    }

    /**
     * @return HasOne<Settings, $this>
     */
    public function settings(): HasOne
    {
        return $this->hasOne(Settings::class);
    }
}
