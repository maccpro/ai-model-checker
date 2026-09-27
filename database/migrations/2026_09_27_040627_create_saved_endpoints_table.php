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
        Schema::create('saved_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('base_url', 500);
            $table->text('api_key')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_endpoints');
    }
};
