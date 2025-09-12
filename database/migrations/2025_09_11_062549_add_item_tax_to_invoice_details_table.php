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
        Schema::table('invoice_details', function (Blueprint $table) {
            $table->decimal('item_tax_per', 5, 2)->default(0)->after('item_discount_amount');
            $table->decimal('item_tax_amount', 12, 2)->default(0)->after('item_tax_per');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_details', function (Blueprint $table) {
            $table->dropColumn(['item_tax_per', 'item_tax_amount']);
        });
    }
};
