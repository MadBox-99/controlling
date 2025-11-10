<?php

declare(strict_types=1);

use App\Models\Kpi;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kpi_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Kpi::class)->constrained()->cascadeOnDelete();
            $table->date('period')->index();
            $table->decimal('planned_value', 15, 2)->nullable();
            $table->decimal('actual_value', 15, 2)->nullable();
            $table->decimal('variance', 15, 2)->nullable();
            $table->decimal('variance_percentage', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_values');
    }
};
