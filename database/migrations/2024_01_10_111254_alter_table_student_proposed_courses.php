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
        Schema::table('student_proposed_courses', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->bigInteger('academic_year_id')->unsigned()->nullable()->change();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_proposed_courses', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->bigInteger('academic_year_id')->unsigned()->nullable(false)->change();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade')->onUpdate('cascade');
        });
    }
};
