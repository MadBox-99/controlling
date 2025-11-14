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
            $table->string('page_path')->nullable()->after('value_type');
            $table->string('metric_type', 50)->nullable()->after('page_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpis', function (Blueprint $table): void {
            $table->dropColumn(['page_path', 'metric_type']);
        });
    }
};
