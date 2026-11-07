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
        Schema::create('soil_measurements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('land_block_id')->constrained('land_blocks')->cascadeOnDelete();
            $table->date('measurement_date');
            $table->string('soil_type', 100)->default('mineral');
            $table->decimal('depth_cm', 6, 2)->default(30.0);
            $table->decimal('bulk_density', 6, 3)->default(1.100);
            $table->decimal('soil_carbon_percent', 6, 3);
            $table->decimal('coarse_fragments_percent', 6, 3)->default(0.0);
            $table->decimal('soil_organic_carbon_t_ha', 10, 4)->default(0);
            $table->string('laboratory', 150)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soil_measurements');
    }
};
