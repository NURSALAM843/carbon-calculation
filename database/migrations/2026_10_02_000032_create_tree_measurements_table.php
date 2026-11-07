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
        Schema::create('tree_measurements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('field_measurement_id')->constrained('field_measurements')->cascadeOnDelete();
            $table->string('tree_tag_number', 50)->nullable();
            $table->string('botanical_species', 150)->index();
            $table->decimal('wood_density_g_cm3', 6, 3)->default(0.570);
            $table->decimal('dbh_cm', 6, 2);
            $table->decimal('total_height_m', 6, 2)->nullable();
            $table->decimal('commercial_height_m', 6, 2)->nullable();
            $table->decimal('crown_diameter_m', 6, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tree_measurements');
    }
};
