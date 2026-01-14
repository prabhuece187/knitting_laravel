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
            if (Schema::hasColumn('outwards', 'job_card_no')) {
                $table->dropColumn('job_card_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outwards', function (Blueprint $table) {
             $table->string('job_card_no')->nullable()->after('expected_dia');
        });
    }
};
