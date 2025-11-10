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
        Schema::create('analytics_settings', function (Blueprint $table): void {
            $table->id();
            $table->json('dimensions')->nullable();
            $table->json('metrics')->nullable();
            $table->string('order_by_type')->nullable();
            $table->json('order_by')->nullable();
            $table->string('order_by_direction')->default('desc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_settings');
    }
};
