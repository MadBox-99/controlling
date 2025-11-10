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
        Schema::create('analytics_conversions', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->string('goal_name');
            $table->integer('goal_completions')->default(0);
            $table->decimal('goal_value', 15, 2)->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->timestamps();

            $table->index('date');
            $table->unique(['date', 'goal_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_conversions');
    }
};
