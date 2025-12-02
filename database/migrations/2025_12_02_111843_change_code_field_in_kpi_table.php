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
            $table->dropUnique(['code']);
        });

        Schema::table('kpis', function (Blueprint $table): void {
            $table->text('code')->change();
        });

        Schema::table('kpis', function (Blueprint $table): void {
            $table->unique('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpis', function (Blueprint $table): void {
            $table->dropUnique(['code']);
        });

        Schema::table('kpis', function (Blueprint $table): void {
            $table->string('code', 50)->change();
        });

        Schema::table('kpis', function (Blueprint $table): void {
            $table->unique('code');
        });
    }
};
