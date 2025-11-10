<?php

declare(strict_types=1);

use App\Models\User;
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
        Schema::create('saved_queries', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(User::class)->index()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('query_type', 50)->index(); // e.g., 'custom', 'predefined'
            $table->json('parameters')->nullable();
            $table->json('query_definition');
            $table->boolean('is_parameterized')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_queries');
    }
};
