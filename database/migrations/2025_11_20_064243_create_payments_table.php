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
        Schema::create('payments', function (Blueprint $table) {
          $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('customer_id');

            $table->date('payment_date')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->enum('payment_type', ['cash','upi','neft','bank','cheque'])->default('cash');

            $table->string('reference_no')->nullable(); // UPI/NEFT/cheque no
            $table->string('bank_name')->nullable();
            $table->date('cheque_date')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();

            // FKs / indexes
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
