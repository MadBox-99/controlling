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
            $table->date('target_date')->nullable()->after('target_value');
            $table->string('goal_type', 50)->nullable()->after('target_date');
            $table->string('value_type', 50)->nullable()->after('goal_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpis', function (Blueprint $table): void {
            $table->dropColumn(['target_date', 'goal_type', 'value_type']);
        });
    }
};
