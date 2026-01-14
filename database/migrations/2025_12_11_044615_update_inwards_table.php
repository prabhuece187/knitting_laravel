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
        Schema::table('inwards', function (Blueprint $table) {

            // Remove unwanted fields
            if (Schema::hasColumn('inwards', 'inward_invoice_no')) {
                $table->dropColumn('inward_invoice_no');
            }
            if (Schema::hasColumn('inwards', 'inward_tin_no')) {
                $table->dropColumn('inward_tin_no');
            }
            if (Schema::hasColumn('inwards', 'total_quantity')) {
                $table->dropColumn('total_quantity');
            }
            if (Schema::hasColumn('inwards', 'status')) {
                $table->dropColumn('status');
            }

            // Add new fields
            $table->string('supplier_invoice_no')->nullable()->after('inward_no');
            $table->string('vehicle_no')->nullable()->after('supplier_invoice_no');
            $table->string('lot_no')->nullable()->after('total_weight');
            $table->integer('no_of_bags')->nullable()->after('lot_no');
            $table->string('received_by')->nullable()->after('no_of_bags');
            $table->text('remarks')->nullable()->after('received_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('inwards', function (Blueprint $table) {
            $table->string('inward_invoice_no')->nullable();
            $table->string('inward_tin_no')->nullable();
            $table->integer('total_quantity')->nullable();
            $table->string('status')->nullable();

            $table->dropColumn([
                'supplier_invoice_no',
                'vehicle_no',
                'lot_no',
                'no_of_bags',
                'received_by',
                'remarks'
            ]);
        });
    }
};
