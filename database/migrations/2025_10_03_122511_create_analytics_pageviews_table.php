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
        Schema::create('analytics_pageviews', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->index();
            $table->text('page_path')->nullable();
            $table->string('page_title')->nullable();
            $table->integer('pageviews')->default(0);
            $table->integer('unique_pageviews')->default(0);
            $table->integer('avg_time_on_page')->default(0);
            $table->integer('entrances')->default(0);
            $table->decimal('bounce_rate', 5, 2)->default(0);
            $table->decimal('exit_rate', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_pageviews');
    }
};
