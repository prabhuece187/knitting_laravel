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
        Schema::create('payment_invoices', function (Blueprint $table) {
           $table->id();

            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('invoice_id');

            $table->decimal('invoice_amount', 14, 2)->default(0); // snapshot of invoice total
            $table->decimal('paid_before', 14, 2)->default(0); // amount paid on invoice before this payment (including initial amount_received + earlier settlements)
            $table->decimal('pay_now', 14, 2)->default(0); // amount applied from this payment to this invoice

            $table->timestamps();

            // FKs
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');

            $table->index(['invoice_id']);
            $table->index(['payment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_invoices');
    }
};
