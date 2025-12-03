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
        Schema::table('settings', function (Blueprint $table): void {
            $table->foreignId('team_id')->nullable()->after('id')->constrained()->cascadeOnDelete();

            // Remove unique constraints as each team will have their own settings
            $table->dropUnique(['property_id']);
            $table->dropUnique(['site_url']);

            // Remove google_service_account as it will be stored globally
            $table->dropColumn('google_service_account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->string('google_service_account')->nullable()->after('id');

            $table->unique(['property_id']);
            $table->unique(['site_url']);

            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });
    }
};
