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
        Schema::create('calculation_inputs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calculation_run_id')->constrained('calculation_runs')->cascadeOnDelete();
            $table->string('input_code', 50)->index();
            $table->decimal('numeric_value', 14, 4);
            $table->string('unit', 50);
            $table->json('source_reference')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calculation_inputs');
    }
};
