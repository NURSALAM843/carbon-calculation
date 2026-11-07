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
        Schema::create('calculation_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calculation_run_id')->unique()->constrained('calculation_runs')->cascadeOnDelete();
            $table->decimal('total_biomass_t', 14, 4)->default(0);
            $table->decimal('total_carbon_t', 14, 4)->default(0);
            $table->decimal('total_co2e_t', 14, 4)->default(0);
            $table->decimal('carbon_stock_t_ha', 10, 4)->default(0);
            $table->decimal('co2e_stock_t_ha', 10, 4)->default(0);
            $table->decimal('sequestration_t_co2e_year', 10, 4)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calculation_results');
    }
};
