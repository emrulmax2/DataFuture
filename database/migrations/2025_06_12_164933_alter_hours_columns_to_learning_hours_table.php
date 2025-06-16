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
        Schema::table('learning_hours', function (Blueprint $table) {
            $table->double('hours', 10, 2)->nullable()->change();
            $table->smallInteger('module_required')->default(0)->after('level_hours_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_hours', function (Blueprint $table) {
            //
        });
    }
};
