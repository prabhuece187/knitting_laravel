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
        Schema::create('knitting_production_returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
            ->constrained('users')
            ->cascadeOnDelete();

            $table->foreignId('job_card_id')
            ->constrained('job_masters')
            ->cascadeOnDelete();

            $table->foreignId('production_id')
            ->constrained('knitting_productions')
            ->cascadeOnDelete();


            $table->string('return_no')->unique(); // human-readable return number
            $table->date('return_date');


            $table->decimal('return_weight', 10, 3); // KG
            $table->enum('return_reason', ['hole','oil_stain','gsm_issue','dia_issue','other']);
            $table->boolean('rework_required')->default(false);

            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knitting_production_returns');
    }
};
