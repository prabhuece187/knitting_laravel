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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('bank_name');
            $table->string('branch_name')->nullable();
            $table->string('account_holder_name');
            $table->string('account_number')->unique();
            $table->string('ifsc_code')->nullable();
            $table->string('bank_city')->nullable();
            $table->string('bank_state')->nullable();
            $table->string('bank_email')->nullable();
            $table->string('bank_mobile')->nullable();
            $table->longText('bank_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
