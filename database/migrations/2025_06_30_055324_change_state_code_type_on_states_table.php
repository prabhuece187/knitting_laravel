<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('states', function (Blueprint $table) {
            $table->unsignedTinyInteger('state_code')->change();
        });
    }

    public function down(): void
    {
        Schema::table('states', function (Blueprint $table) {
            $table->string('state_code')->change();
        });
    }
};
