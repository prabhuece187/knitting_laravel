<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outward_details', function (Blueprint $table) {

            // fabric weight for knitting stock calculation
            $table->decimal('fabric_weight', 10, 3)
                  ->default(0)
                  ->after('net_weight');

            // job card relation (single source of truth)
            $table->unsignedBigInteger('job_card_id')
                  ->nullable()  
                  ->after('outward_id');

            $table->foreign('job_card_id')
                  ->references('id')
                  ->on('job_masters')   // 🔴 CHANGE IF YOUR TABLE NAME IS DIFFERENT
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('outward_details', function (Blueprint $table) {
            $table->dropForeign(['job_card_id']);
            $table->dropColumn(['job_card_id', 'fabric_weight']);
        });
    }
};
