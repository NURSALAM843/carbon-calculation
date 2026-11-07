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
        Schema::create('calculation_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('estate_id')->constrained('estates')->cascadeOnDelete();
            $table->foreignUuid('land_block_id')->constrained('land_blocks')->cascadeOnDelete();
            $table->foreignUuid('methodology_version_id')->constrained('methodology_versions');
            $table->foreignUuid('executed_by')->constrained('users');
            $table->string('ecosystem_type', 50)->index();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('area_ha', 10, 4);
            $table->string('calculation_type', 30)->default('stock_only');
            $table->string('status', 30)->default('draft')->index();
            $table->json('input_snapshot');
            $table->json('formula_snapshot');
            $table->json('parameter_snapshot');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calculation_runs');
    }
};
