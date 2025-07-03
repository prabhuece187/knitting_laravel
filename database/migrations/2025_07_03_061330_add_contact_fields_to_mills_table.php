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
        Schema::table('mills', function (Blueprint $table) {
            $table->string('mobile_number')->nullable()->after('mill_name'); // adjust position if needed
            $table->text('address')->nullable()->after('mobile_number');
            $table->text('description')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mills', function (Blueprint $table) {
            $table->dropColumn(['mobile_number', 'address', 'description']);
        });
    }
};
