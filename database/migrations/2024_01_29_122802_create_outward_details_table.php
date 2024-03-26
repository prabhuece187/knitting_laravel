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
        Schema::create('outward_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('outward_id')
            ->constrained('outwards')
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
            $table->integer('outward_qty');
            $table->double('outward_weight', 15,2);
            $table->double('deliverd_weight', 15,2);
            $table->date('outward_detail_date')->format('d/m/Y');

            $table->string('yarn_colour');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outward_details');
    }
};
