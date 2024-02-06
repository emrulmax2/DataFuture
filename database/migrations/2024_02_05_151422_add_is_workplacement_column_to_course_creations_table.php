<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_creations', function (Blueprint $table) {
            $table->tinyInteger('is_workplacement')->default(0)->after('reg_fees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_creations', function (Blueprint $table) {
            $table->dropColumn('is_workplacement');
        });
    }
};
