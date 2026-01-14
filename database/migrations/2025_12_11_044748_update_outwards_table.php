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
        Schema::table('outwards', function (Blueprint $table) {

            // REMOVE unwanted
            if (Schema::hasColumn('outwards', 'outward_tin_no')) {
                $table->dropColumn('outward_tin_no');
            }
            if (Schema::hasColumn('outwards', 'total_quantity')) {
                $table->dropColumn('total_quantity');
            }
            if (Schema::hasColumn('outwards', 'yarn_send')) {
                $table->dropColumn('yarn_send');
            }

            // ADD new fields
            $table->string('vehicle_no')->nullable()->after('outward_invoice_no');
            $table->string('process_type')->nullable()->after('total_weight');
            $table->string('expected_gsm')->nullable()->after('process_type');
            $table->string('expected_dia')->nullable()->after('expected_gsm');
            $table->string('job_card_no')->nullable()->after('expected_dia');
            $table->text('remarks')->nullable()->after('job_card_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('outwards', function (Blueprint $table) {
            $table->string('outward_tin_no')->nullable();
            $table->integer('total_quantity')->nullable();
            $table->string('yarn_send')->nullable();

            $table->dropColumn([
                'vehicle_no',
                'process_type',
                'expected_gsm',
                'expected_dia',
                'job_card_no',
                'remarks'
            ]);
        });
    }
};
