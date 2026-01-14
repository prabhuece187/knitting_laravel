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
         Schema::table('outward_details', function (Blueprint $table) {

            // REMOVE unwanted
            if (Schema::hasColumn('outward_details', 'outward_detail_date')) {
                $table->dropColumn('outward_detail_date');
            }
            if (Schema::hasColumn('outward_details', 'deliverd_weight')) {
                $table->dropColumn('deliverd_weight');
            }

            // ADD new fields
            $table->string('lot_no')->nullable()->after('yarn_colour');
            $table->string('bag_no')->nullable()->after('lot_no');
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
       Schema::table('outward_details', function (Blueprint $table) {
            $table->date('outward_detail_date')->nullable();
            $table->decimal('deliverd_weight', 10, 3)->nullable();

            $table->dropColumn([
                'lot_no',
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
