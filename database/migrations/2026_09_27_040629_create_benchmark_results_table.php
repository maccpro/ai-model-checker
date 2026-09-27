<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('benchmark_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('benchmark_run_id')->constrained('benchmark_runs')->cascadeOnDelete();
            $table->string('model_name');
            $table->string('base_url', 500);
            $table->longText('response_content')->nullable();
            $table->unsignedInteger('ttft_ms')->nullable();
            $table->unsignedInteger('total_duration_ms')->nullable();
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->unsignedInteger('total_tokens')->nullable();
            $table->decimal('tokens_per_second', 8, 2)->nullable();
            $table->string('status', 30)->default('success');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['benchmark_run_id', 'model_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benchmark_results');
    }
};
