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
         Schema::table('inward_details', function (Blueprint $table) {

            // REMOVE unwanted
            if (Schema::hasColumn('inward_details', 'inward_qty')) {
                $table->dropColumn('inward_qty');
            }
            if (Schema::hasColumn('inward_details', 'inward_detail_date')) {
                $table->dropColumn('inward_detail_date');
            }

            // ADD new
            $table->string('bag_no')->nullable()->after('yarn_colour');
            $table->decimal('gross_weight', 10, 3)->nullable()->after('bag_no');
            $table->decimal('tare_weight', 10, 3)->nullable()->after('gross_weight');
            $table->decimal('net_weight', 10, 3)->nullable()->after('tare_weight');
            $table->string('uom')->default('kg')->after('net_weight');
            $table->text('remarks')->nullable()->after('uom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inward_details', function (Blueprint $table) {
            $table->decimal('inward_qty', 10, 3)->nullable();
            $table->date('inward_detail_date')->nullable();

            $table->dropColumn([
                'bag_no',
                'gross_weight',
                'tare_weight',
                'net_weight',
                'uom',
                'remarks'
            ]);
        });
    }
};
