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
        Schema::create('analytics_events', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->index();
            $table->string('event_name')->index();
            $table->string('event_category');
            $table->string('event_action');
            $table->string('event_label')->nullable();
            $table->integer('event_count')->default(0);
            $table->decimal('event_value', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
