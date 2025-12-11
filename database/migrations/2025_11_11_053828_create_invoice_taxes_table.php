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
        Schema::create('invoice_taxes', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

             // Link to main invoice
            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->onDelete('cascade');

            // 'CGST', 'SGST', or 'IGST'
            $table->string('tax_type', 10);

            // Tax rate (example: 9.00 or 1.50)
            $table->decimal('tax_rate', 5, 2);

            // Calculated amount for that rate and type (₹190.68 etc.)
            $table->decimal('tax_amount', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_taxes');
    }
};
