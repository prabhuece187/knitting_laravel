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
        Schema::create('knitting_productions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
            ->constrained('users')
            ->cascadeOnDelete();

             $table->foreignId('job_card_id')
            ->constrained('job_masters')
            ->cascadeOnDelete();

             $table->foreignId('machine_id')
            ->constrained('knitting_machines')
            ->cascadeOnDelete();
            
            $table->string('production_no')->unique();

            $table->date('production_date');

            $table->string('shift')->nullable(); // A / B / C (optional)

            $table->string('operator_name')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knitting_productions');
    }
};
