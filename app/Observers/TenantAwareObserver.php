<?php

declare(strict_types=1);

namespace App\Observers;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

final class TenantAwareObserver
{
    /**
     * Handle the Model "creating" event.
     *
     * Automatically assigns the current tenant's ID to the model's team_id field.
     */
    public function creating(Model $model): void
    {
        if ($tenant = Filament::getTenant()) {
            $model->team_id = $tenant->id;
        }
    }
}
