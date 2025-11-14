<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Kpi;
use App\Models\User;
use Filament\Facades\Filament;

final class KpiPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view kpis');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Kpi $kpi): bool
    {
        if (! $user->can('view kpis')) {
            return false;
        }

        // Check if KPI belongs to current tenant
        $tenant = Filament::getTenant();

        return $tenant && $kpi->team_id === $tenant->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create kpis');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Kpi $kpi): bool
    {
        if (! $user->can('update kpis')) {
            return false;
        }

        // Check if KPI belongs to current tenant
        $tenant = Filament::getTenant();

        return $tenant && $kpi->team_id === $tenant->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Kpi $kpi): bool
    {
        if (! $user->can('delete kpis')) {
            return false;
        }

        // Check if KPI belongs to current tenant
        $tenant = Filament::getTenant();

        return $tenant && $kpi->team_id === $tenant->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Kpi $kpi): bool
    {
        return $user->can('update kpis');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Kpi $kpi): bool
    {
        return $user->can('delete kpis');
    }
}
