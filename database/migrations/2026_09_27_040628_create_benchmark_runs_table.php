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
        Schema::create('benchmark_runs', function (Blueprint $table) {
            $table->id();
            $table->string('mode', 50)->default('playground');
            $table->string('category', 50)->nullable();
            $table->text('prompt');
            $table->text('system_prompt')->nullable();
            $table->json('parameters')->nullable();
            $table->timestamps();

            $table->index(['mode', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benchmark_runs');
    }
};
