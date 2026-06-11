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
        Schema::table('yarn_types', function (Blueprint $table) {
            $table->string('yarn_gauge')->nullable()->after('yarn_type');
            $table->decimal('yarn_dia', 8, 2)->nullable()->after('yarn_gauge');
            $table->decimal('yarn_gsm', 8, 2)->nullable()->after('yarn_dia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yarn_types', function (Blueprint $table) {
            $table->dropColumn(['yarn_gauge', 'yarn_dia', 'yarn_gsm']);
        });
    }
};
