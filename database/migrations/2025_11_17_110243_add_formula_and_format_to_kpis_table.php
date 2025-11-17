<?php

declare(strict_types=1);

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
        Schema::table('kpis', function (Blueprint $table): void {
            // Check if columns don't already exist before adding them
            if (! Schema::hasColumn('kpis', 'formula')) {
                // Add formula field (nullable for calculated KPIs)
                $table->text('formula')->nullable()->after('category');
            }

            if (! Schema::hasColumn('kpis', 'format')) {
                // Add format field (required - number, percentage, ratio, duration)
                $table->string('format', 50)->default('number')->after('formula');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpis', function (Blueprint $table): void {
            $table->dropColumn(['formula', 'format']);
        });
    }
};
