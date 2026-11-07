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
        Schema::create('parameter_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calculation_parameter_id')->constrained('calculation_parameters')->cascadeOnDelete();
            $table->decimal('numeric_value', 12, 6)->nullable();
            $table->json('value_json')->nullable();
            $table->string('source', 255);
            $table->string('source_reference', 255)->nullable();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameter_versions');
    }
};
