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
        Schema::create('analytics_sessions', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->integer('sessions')->default(0);
            $table->integer('users')->default(0);
            $table->integer('new_users')->default(0);
            $table->decimal('bounce_rate', 5, 2)->default(0);
            $table->integer('avg_session_duration')->default(0);
            $table->decimal('pages_per_session', 5, 2)->default(0);
            $table->string('source')->nullable();
            $table->string('medium')->nullable();
            $table->string('campaign')->nullable();
            $table->timestamps();

            $table->index('date');
            $table->unique(['date', 'source', 'medium', 'campaign']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_sessions');
    }
};
