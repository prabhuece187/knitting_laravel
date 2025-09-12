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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
            ->constrained('customers')
            ->cascadeOnDelete();

            $table->foreignId('user_id')
            ->constrained('users')
            ->cascadeOnDelete();

            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->string('payment_terms')->nullable();
            $table->date('due_date')->nullable();

            $table->text('invoice_notes')->nullable();
            $table->text('invoice_terms')->nullable();

            // amounts
            $table->decimal('invoice_subtotal', 15, 2)->default(0);
            $table->decimal('invoice_taxable_value', 15, 2)->default(0);
            $table->decimal('invoice_total', 15, 2)->default(0);

            // taxes
            $table->decimal('invoice_cgst', 15, 2)->default(0);
            $table->decimal('invoice_sgst', 15, 2)->default(0);
            $table->decimal('invoice_igst', 15, 2)->default(0);

            // discounts
            $table->decimal('bill_discount_per', 8, 2)->default(0);
            $table->decimal('bill_discount_amount', 15, 2)->default(0);
            $table->enum('bill_discount_type', ['percent', 'amount'])->nullable();

            // payments
            $table->decimal('amount_received', 15, 2)->default(0);
            $table->decimal('balance_amount', 15, 2)->default(0);
            $table->decimal('round_off', 15, 2)->default(0);
            $table->string('amount_received_type')->nullable();

            // status
            $table->boolean('fully_paid')->default(false);

            // subtotal level
            $table->decimal('subtotal_discount', 15, 2)->default(0);
            $table->decimal('subtotal_tax', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
