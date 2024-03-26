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
        Schema::create('outwards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
            ->constrained('customers')
            ->cascadeOnDelete();

            $table->foreignId('user_id')
            ->constrained('users')
            ->cascadeOnDelete();

            $table->foreignId('mill_id')
            ->constrained('mills')
            ->cascadeOnDelete();

            $table->foreignId('inward_id')
            ->constrained('inwards')
            ->cascadeOnDelete();

            $table->string('outward_no')->unique();
            $table->string('outward_invoice_no')->unique();
            $table->string('outward_tin_no')->nullable(true);
            $table->date('outward_date')->format('d/m/Y');

            $table->double('total_weight', 15,2);
            $table->bigInteger('total_quantity');

            $table->string('outward_vehicle_no')->nullable(true);

            $table->string('yarn_send')->nullable(true);

            $table->Integer('status');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outwards');
    }
};
