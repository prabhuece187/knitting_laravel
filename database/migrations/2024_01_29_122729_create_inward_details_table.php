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
        Schema::create('inward_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inward_id')
            ->constrained('inwards')
            ->cascadeOnDelete();

            $table->foreignId('user_id')
            ->constrained('users')
            ->cascadeOnDelete();

            $table->foreignId('item_id')
            ->constrained('items')
            ->cascadeOnDelete();

            $table->foreignId('yarn_type_id')
            ->constrained('yarn_types')
            ->cascadeOnDelete();

            $table->integer('yarn_dia');
            $table->integer('yarn_gsm');
            $table->string('yarn_gauge');
            $table->integer('inward_qty');
            $table->double('inward_weight', 15,2);
            $table->date('inward_detail_date')->format('d/m/Y');

            $table->string('yarn_colour');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inward_details');
    }
};
