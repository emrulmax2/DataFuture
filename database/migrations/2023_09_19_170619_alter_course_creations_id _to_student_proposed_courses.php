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
            $table->dropForeign(['course_creations_id']);
            $table->renameColumn('course_creations_id','course_creation_id');
            $table->foreign('course_creation_id')->references('id')->on('course_creations')->onDelete('cascade')->onUpdate('cascade');

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
            $table->dropForeign(['course_creation_id']);
            $table->renameColumn('course_creation_id','course_creations_id');
            $table->foreign('course_creations_id')->references('id')->on('course_creations')->onDelete('cascade')->onUpdate('cascade');

        });
    }
};
