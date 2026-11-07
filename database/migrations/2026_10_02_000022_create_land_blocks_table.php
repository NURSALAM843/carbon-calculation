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
        Schema::create('land_blocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('estate_id')->constrained('estates')->cascadeOnDelete();
            $table->string('code', 50)->index();
            $table->string('name');
            $table->string('ecosystem_type', 50)->default('oil_palm')->index();
            $table->decimal('area_ha', 10, 4);
            $table->json('geometry')->nullable();
            $table->string('soil_type', 50)->default('mineral');
            $table->string('land_type', 50)->default('palm_productive');
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('land_blocks');
    }
};
