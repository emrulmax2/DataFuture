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
            $table->tinyInteger('has_evening_and_weekend')->default(0)->after('required_hours')->comment('0=No, 1=Yes');
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
            $table->dropColumn('has_evening_and_weekend');
        });
    }
};
