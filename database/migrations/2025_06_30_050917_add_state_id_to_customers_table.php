<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Safely drop columns if they exist
            if (Schema::hasColumn('customers', 'customer_state')) {
                $table->dropColumn('customer_state');
            }

            if (Schema::hasColumn('customers', 'customer_state_code')) {
                $table->dropColumn('customer_state_code');
            }

            // Safely add foreign key if it doesn't already exist
            if (!Schema::hasColumn('customers', 'state_id')) {
                $table->foreignId('state_id')
                      ->nullable()
                      ->after('user_id')
                      ->constrained('states')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'state_id')) {
                $table->dropForeign(['state_id']);
                $table->dropColumn('state_id');
            }

            // Re-add the old columns only if they don't exist
            if (!Schema::hasColumn('customers', 'customer_state')) {
                $table->string('customer_state');
            }

            if (!Schema::hasColumn('customers', 'customer_state_code')) {
                $table->string('customer_state_code');
            }
        });
    }
};
