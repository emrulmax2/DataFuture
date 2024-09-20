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
        Schema::table('task_lists', function (Blueprint $table) {
            $table->enum('pearson_reg', ['Yes', 'No'])->default('No')->after('attendance_excuses')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_lists', function (Blueprint $table) {
            //
        });
    }
};
