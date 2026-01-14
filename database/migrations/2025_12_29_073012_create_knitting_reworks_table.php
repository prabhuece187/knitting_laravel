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
        Schema::create('knitting_reworks', function (Blueprint $table) {
            $table->id();

             $table->foreignId('user_id')
            ->constrained('users')
            ->cascadeOnDelete();

            $table->foreignId('production_return_id')
            ->constrained('knitting_production_returns')
            ->cascadeOnDelete();

            $table->foreignId('job_card_id')
            ->constrained('job_masters')
            ->cascadeOnDelete();

            $table->string('rework_no')->unique();
            $table->date('rework_date');
            
            $table->decimal('rework_weight',10,3);
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knitting_reworks');
    }
};
