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
        Schema::create('carbon_pool_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calculation_result_id')->constrained('calculation_results')->cascadeOnDelete();
            $table->foreignUuid('carbon_pool_id')->constrained('carbon_pools')->cascadeOnDelete();
            $table->decimal('biomass_t', 14, 4)->default(0);
            $table->decimal('carbon_t', 14, 4)->default(0);
            $table->decimal('co2e_t', 14, 4)->default(0);
            $table->decimal('co2e_per_ha', 10, 4)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carbon_pool_results');
    }
};
