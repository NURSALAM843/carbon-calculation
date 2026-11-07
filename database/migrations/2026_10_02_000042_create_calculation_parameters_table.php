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
        Schema::create('calculation_parameters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('methodology_version_id')->constrained('methodology_versions')->cascadeOnDelete();
            $table->string('code', 50)->index();
            $table->string('name');
            $table->string('unit', 50)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calculation_parameters');
    }
};
