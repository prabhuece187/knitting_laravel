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
        Schema::create('knitting_machines', function (Blueprint $table) {
           
            $table->id();

            $table->foreignId('user_id')
            ->constrained('users')
            ->cascadeOnDelete();

            $table->string('machine_no')->unique(); // Machine No shown to user
            $table->string('machine_name')->nullable();
            $table->string('brand')->nullable();     // FONGS / DMS etc
            $table->string('model')->nullable();
            $table->integer('dia')->nullable();      // 28, 30, 32
            $table->integer('gauge')->nullable();    // 18, 20, 24
            $table->integer('feeder')->nullable();

            $table->enum('status', ['active', 'maintenance', 'inactive'])
                  ->default('active');

            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knitting_machines');
    }
};
