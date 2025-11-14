<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\AnalyticsConversion;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Models\Kpi;
use App\Models\SearchPage;
use App\Models\SearchQuery;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

final class ApplyTenantScopes
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $tenant = Filament::getTenant();

        if ($tenant) {
            // Apply global scope to all tenant-scoped models
            $models = [
                Kpi::class,
                SearchPage::class,
                SearchQuery::class,
                AnalyticsPageview::class,
                AnalyticsSession::class,
                AnalyticsEvent::class,
                AnalyticsConversion::class,
            ];

            foreach ($models as $model) {
                $model::addGlobalScope(
                    'team',
                    fn ($query) => $query->where('team_id', $tenant->id),
                );
            }
        }

        return $next($request);
    }
}
