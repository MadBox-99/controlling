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
        Schema::create('search_pages', function (Blueprint $table): void {
            $table->id();

            $table->date('date');
            $table->text('page_url');
            $table->string('country', 20)->nullable();
            $table->string('device', 20)->index();
            $table->integer('impressions')->default(0);
            $table->integer('clicks')->default(0);
            $table->decimal('ctr', 5, 2)->default(0);
            $table->decimal('position', 5, 2)->default(0);
            $table->timestamps();

            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_pages');
    }
};
