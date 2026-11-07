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
        Schema::create('palm_measurements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('field_measurement_id')->constrained('field_measurements')->cascadeOnDelete();
            $table->integer('sample_count')->default(1);
            $table->decimal('avg_height_m', 6, 2)->default(0);
            $table->decimal('avg_stem_diameter_cm', 6, 2)->default(0);
            $table->decimal('avg_frond_count', 6, 2)->default(0);
            $table->decimal('avg_frond_biomass_kg', 8, 2)->nullable();
            $table->decimal('avg_fruit_bunch_biomass_kg', 8, 2)->nullable();
            $table->decimal('annual_pruned_fronds', 6, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('palm_measurements');
    }
};
