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
        Schema::create('palm_stands', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('crop_cycle_id')->constrained('crop_cycles')->cascadeOnDelete();
            $table->integer('tree_count');
            $table->decimal('density_per_ha', 8, 2);
            $table->string('growth_stage', 30)->default('TM_PRIMA');
            $table->decimal('average_height_m', 6, 2)->default(0);
            $table->decimal('average_stem_diameter_cm', 6, 2)->default(0);
            $table->integer('age_years');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('palm_stands');
    }
};
