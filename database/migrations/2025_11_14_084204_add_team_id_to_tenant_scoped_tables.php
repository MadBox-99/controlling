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
        $tables = [
            'kpis',
            'search_pages',
            'search_queries',
            'analytics_pageviews',
            'analytics_sessions',
            'analytics_events',
            'analytics_conversions',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->foreignId('team_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'kpis',
            'search_pages',
            'search_queries',
            'analytics_pageviews',
            'analytics_sessions',
            'analytics_events',
            'analytics_conversions',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->dropForeign(['team_id']);
                $table->dropColumn('team_id');
            });
        }
    }
};
