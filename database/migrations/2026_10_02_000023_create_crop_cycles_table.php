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
        Schema::create('crop_cycles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('land_block_id')->constrained('land_blocks')->cascadeOnDelete();
            $table->string('crop_type', 50)->default('Oil Palm');
            $table->date('planting_date')->nullable();
            $table->integer('planting_year')->nullable()->index();
            $table->integer('initial_population')->nullable();
            $table->decimal('planting_density', 8, 2)->nullable();
            $table->string('variety', 100)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crop_cycles');
    }
};
