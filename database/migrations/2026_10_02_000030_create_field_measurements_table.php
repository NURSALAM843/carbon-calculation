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
        Schema::create('field_measurements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('land_block_id')->constrained('land_blocks')->cascadeOnDelete();
            $table->date('measurement_date');
            $table->integer('measurement_year')->index();
            $table->string('measurement_method', 50)->default('sample_plot_census');
            $table->decimal('plot_area_m2', 10, 2)->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignUuid('measured_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_measurements');
    }
};
