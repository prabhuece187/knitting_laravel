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
        Schema::table('additional_charges', function (Blueprint $table) {
            $table->renameColumn('additional_name', 'additional_charge_name');
            $table->renameColumn('additional_amount', 'additional_charge_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('additional_charges', function (Blueprint $table) {
             $table->renameColumn('additional_charge_name', 'additional_name');
            $table->renameColumn('additional_charge_amount', 'additional_amount');
        });
    }
};
