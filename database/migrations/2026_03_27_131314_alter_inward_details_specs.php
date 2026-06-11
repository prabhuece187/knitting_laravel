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
        // ✅ Step 1: Convert existing data (safety - optional but good)
        DB::statement("UPDATE inward_details SET yarn_dia = yarn_dia * 1");
        DB::statement("UPDATE inward_details SET yarn_gsm = yarn_gsm * 1");

        // ✅ Step 2: Alter columns
        Schema::table('inward_details', function (Blueprint $table) {
            $table->decimal('yarn_dia', 5, 2)->change();
            $table->decimal('yarn_gsm', 6, 2)->change();
            $table->string('yarn_gauge', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inward_details', function (Blueprint $table) {
            $table->integer('yarn_dia')->change();
            $table->integer('yarn_gsm')->change();
            $table->string('yarn_gauge', 255)->change();
        });
    }
};
