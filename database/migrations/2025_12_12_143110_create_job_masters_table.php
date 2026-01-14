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
        Schema::create('job_masters', function (Blueprint $table) {
                $table->id();

                // references
                $table->unsignedBigInteger('inward_id');
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('mill_id');
                $table->unsignedBigInteger('user_id'); // created by

                // job entry fields
                $table->string('job_card_no')->unique();
                $table->date('job_date');

                $table->decimal('approx_job_weight', 10, 2)->nullable();
                $table->text('remarks')->nullable();
                $table->date('expected_delivery_date')->nullable();

                // status
                $table->enum('status', ['open', 'completed'])
                    ->default('open');

                $table->timestamps();

                // foreign keys
                $table->foreign('inward_id')->references('id')->on('inwards');
                $table->foreign('customer_id')->references('id')->on('customers');
                $table->foreign('mill_id')->references('id')->on('mills');
                $table->foreign('user_id')->references('id')->on('users');
                    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_masters');
    }
};
