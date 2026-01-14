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
        Schema::create('knitting_production_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
            ->constrained('users')
            ->cascadeOnDelete();

             $table->foreignId('knitting_production_id')
            ->constrained('knitting_productions')
            ->cascadeOnDelete();

            $table->decimal('produced_weight', 10, 2); // KG
            $table->integer('rolls_count')->nullable();

            $table->decimal('dia', 5, 2)->nullable();
            $table->decimal('gsm', 6, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knitting_production_details');
    }
};
