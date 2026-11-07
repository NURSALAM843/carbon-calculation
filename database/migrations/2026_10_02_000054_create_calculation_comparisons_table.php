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
        Schema::create('calculation_comparisons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calculation_run_id')->constrained('calculation_runs')->cascadeOnDelete();
            $table->foreignUuid('previous_calculation_run_id')->constrained('calculation_runs')->cascadeOnDelete();
            $table->decimal('delta_carbon_t', 14, 4);
            $table->decimal('delta_co2e_t', 14, 4);
            $table->decimal('annualized_co2e_t', 14, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calculation_comparisons');
    }
};
