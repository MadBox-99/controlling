<?php

declare(strict_types=1);

use App\Models\Report;
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
        Schema::create('report_executions', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Report::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->json('parameters')->nullable();
            $table->timestamp('executed_at');
            $table->integer('execution_time_ms');
            $table->integer('row_count');

            $table->index('report_id');
            $table->index('user_id');
            $table->index('executed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_executions');
    }
};
